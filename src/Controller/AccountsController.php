<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\MonthAccount;
use App\Entity\PaymentItem;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;
use App\Entity\User;
use App\Entity\Enrollment;
use App\Entity\Student;
use App\Entity\OptionalsAttendance;
use App\Entity\AccountInvoice;
use App\Entity\AccountReceipt;
use App\Entity\SmartReceipt;
use App\Entity\Payment;
use App\Entity\PaymentProof;
use App\Entity\TransportTrip;

#form type definition
use App\Form\PaymentItemType;
use App\Form\AccountInvoiceType;
use App\Form\AccountInvoiceNumberType;
use App\Form\AccountInvoiceReceiptType;
use App\Form\AccountSmartReceiptType;
use App\Form\UserMyaccountSmartProofType;
use App\Form\SmartPayType;
use App\Form\SmartGenerateType;

use Symfony\Component\Form\Extension\Core\Type\FileType;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AccountsController extends Controller
{
    /**
     * @Route("/accounts", name="accounts")
     */
    public function index()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $currentUnits = $currentSchoolYear->getSchoolunits();

        $allStudents = $this->getDoctrine()->getRepository
        (Student::class)->findAllYear($currentSchoolYear);

        return $this->render('accounts/accounts.html.twig', [
            'current_year' => $currentSchoolYear,
            'current_units' => $currentUnits,
            'sorted_students' => $allStudents,
        ]);
    }

    /**
     * @Route("/invoices", name="invoices")
     */
    public function invoices()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $currentUnits = $currentSchoolYear->getSchoolunits();

        $allStudents = $this->getDoctrine()->getRepository
        (Student::class)->findAllYear($currentSchoolYear);

        return $this->render('accounts/view.all.invoices.html.twig', [
            'current_year' => $currentSchoolYear,
            'current_units' => $currentUnits,
            'sorted_students' => $allStudents,
        ]);
    }

    /**
     * @Route("/payments", name="payments")
     */
    public function payments()
    {
        $pending_payments = $this->getDoctrine()->getRepository
        (Payment::class)->findBy(['isPending' => true], ['payDate' => 'DESC']);

        $confirmed_payments = $this->getDoctrine()->getRepository
        (Payment::class)->findBy(['isConfirmed' => true], ['payDate' => 'DESC']);

        $rejected_payments = $this->getDoctrine()->getRepository
        (Payment::class)->findBy(['isPending' => false, 'isConfirmed' => false], ['payDate' => 'DESC']);

        return $this->render('accounts/payments.html.twig', [
            'pending_payments' => $pending_payments,
            'confirmed_payments' => $confirmed_payments,
            'rejected_payments' => $rejected_payments,
        ]);
    }

    /**
     * @Route("/smartpay/{accId}/{edit}", name="smart_pay")
     */
    public function smart_pay(Request $request, $accId, $edit, \Swift_Mailer $mailer)
    {
      $monthAccount = $this->getDoctrine()->getRepository
      (MonthAccount::class)->find($accId);
      //return $this->redirectToRoute('account_invoices', array('accId' => $accId));

      if ($this->getUser()->getUsertype() == 'ROLE_PARENT') {
        if ($monthAccount->getStudent()->getUser()->getGuardian()->getUser() != $this->getUser()) {
          return $this->redirectToRoute('myaccount_invoices');
        }
      }

      if ($edit == 'add') {
        $newPayment = new Payment();

        //$newPayment->setPayAmount(0);
        $newPayment->setPayDate(new \DateTime('now'));
        $newPayment->setPayMethod('single');
        $newPayment->setIsPending(true);
        $newPayment->setIsConfirmed(false);
      } else {
        $newPayment = $this->getDoctrine()->getRepository
        (Payment::class)->find($edit);
        $newPayment->setPayAmount($newPayment->getPayAmount()+$newPayment->getPayAdvance());
      }

      $guardian = $monthAccount->getStudent()->getUser()->getGuardian();
      $children = $guardian->getChildren();

      $availableBalance = 0;
      $payableInvoices = array();

      foreach($children as $child) {
        $student = $child->getChildLatestEnroll()->getStudent();
        if ($student) {
          foreach($student->getMonthAccounts() as $account) {
            $availableBalance = $availableBalance + $account->getAdvanceBalance();
            if ($account->getTotalPrice() != $account->getTotalPaid()) {
              foreach($account->getAccountInvoices() as $invoice) {
                //TODO - should be able to just lookup the isPaid value, but we need to make sure
                //       that this is doable with the old system still in place
                if ($invoice->getInvoicePaid() < $invoice->getInvoiceTotal() && $invoice->getIsLocked()) {
                  $payableInvoices[$invoice->getInvoiceName()] = $invoice;
                }
              }
            }
          }
        }
      }

      if (count($payableInvoices) == 0) {
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Nu există facturi salvate în așteptare!'
        );
        return $this->redirectToRoute('account_invoices', array('accId' => $accId));
      }


      $form = $this->createForm(SmartPayType::Class, $newPayment, array(
        'invoices' => $payableInvoices,
      ));

      $hasPayProof = true;

      if ($edit != 'add') {
        $form->remove('payProof');
        $hasPayProof = false;
      }

      if ($this->getUser()->getUsertype() != 'ROLE_PARENT') {
        $form->remove('payProof');
        $hasPayProof = false;
      }

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) {
        $thePayment = $form->getData();

        if ($thePayment->getPayAdvance() != 0) {
          // !!!!!!!! IMPORTANT !!!!!!!!!
          // the following line controls what we put in the amount field on the frontend (tot or tot+adv)
          $thePayment->setPayAmount($thePayment->getPayAmount() - $thePayment->getPayAdvance());
        }

        if ($thePayment->getPayMethod() == 'single') {
          //START checks
          if ($thePayment->getPayInvoices()->count() == 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu ai selectat nicio factură. Te rugăm să selectezi factura pe care dorești să o plătești.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          $invoice = $thePayment->getPayInvoices()->first();
          $invoiceRemaining = $invoice->getInvoiceTotal() - $invoice->getInvoicePaid();
          if ($thePayment->getPayAmount() < $invoiceRemaining) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Plata făcută este mai mică decât suma totală a facturii. Te rugăm să corectezi suma sau să selectezi 1x Factură (parțial).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          } elseif ($thePayment->getPayAmount() > $invoiceRemaining) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Plata făcută este mai mare decât suma totală a facturii. Dacă vrei să achiți în avans, te rugăm să specifici diferența de '
                .($thePayment->getPayAmount() - $invoiceRemaining).' RON în căsuța Avans.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          //END checks;
        }


        if ($thePayment->getPayMethod() == 'partial') {
          //START checks
          if ($thePayment->getPayInvoices()->count() == 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu ai selectat nicio factură. Te rugăm să selectezi factura pe care dorești să o plătești.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          if ($thePayment->getPayAdvance() != 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu poți să adaugi avans când achiți o factură parțial.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          $invoice = $thePayment->getPayInvoices()->first();
          $invoiceRemaining = $invoice->getInvoiceTotal() - $invoice->getInvoicePaid();
          if ($thePayment->getPayAmount() >= $invoiceRemaining) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Plata făcută este mai mare sau egală decât suma totală a facturii. Te rugăm să corectezi suma sau să selectezi 1x Factură (integral).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          //END checks
        }

        if ($thePayment->getPayMethod() == 'multiple') {
          //START checks
          if ($thePayment->getPayInvoices()->count() == 1) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Ai selectat 1x factură. Te rugăm să selectezi 2x sau mai multe sau să selectezi 1x Factură (integral).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          if ($thePayment->getPayInvoices()->count() == 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu ai selectat nicio factură. Te rugăm să selectezi 2x sau mai multe sau să selectezi opțiunea 1x Factură (integral).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          $invoices = $thePayment->getPayInvoices();
          $invoicesRemaining = 0;
          $invoiceUnits = array(); //check different units
          foreach ($invoices as $invoice) {
            $invoicesRemaining = $invoicesRemaining + $invoice->getInvoiceTotal() - $invoice->getInvoicePaid();
            $unit = $invoice->getMonthAccount()->getStudent()->getSchoolUnit();
            if (!in_array($unit, $invoiceUnits)) { $invoiceUnits[] = $unit; }
          }
          if (count($invoiceUnits)>1) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu poți achita facturi de la unități școlare diferite. Te rugăm să efectuezi plăți individuale sau să selectezi doar facturi aferente
                aceleiași unități școlare.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          if ($thePayment->getPayAmount() < $invoicesRemaining) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Plata făcută este mai mică decât suma totală a facturilor. Te rugăm să corectezi suma sau să selectezi Facturi multiple (parțial).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          } elseif ($thePayment->getPayAmount() > $invoicesRemaining) {
              $this->get('session')->getFlashBag()->add(
                  'notice',
                  'Plata făcută este mai mare decât suma totală a facturilor. Dacă vrei să achiți în avans, te rugăm să specifici diferența de '
                  .($thePayment->getPayAmount() - $invoicesRemaining).' RON în căsuța Avans.'
              );
              return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          //END checks
        }

        if ($thePayment->getPayMethod() == 'multiple_partial') {
          //START checks
          if ($thePayment->getPayInvoices()->count() == 1) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Ai selectat 1x factură. Te rugăm să selectezi 2x sau mai multe sau să selectezi 1x Factură (parțial).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          if ($thePayment->getPayInvoices()->count() == 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu ai selectat nicio factură. Te rugăm să selectezi 2x sau mai multe sau să selectezi opțiunea 1x Factură (parțial).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          if ($thePayment->getPayAdvance() != 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu poți să adaugi avans când achiți facturi parțial.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          $invoices = $thePayment->getPayInvoices();
          $invoicesRemaining = 0;
          $invoiceUnits = array(); //check different units
          foreach ($invoices as $invoice) {
            $invoicesRemaining = $invoicesRemaining + $invoice->getInvoiceTotal() - $invoice->getInvoicePaid();
            $unit = $invoice->getMonthAccount()->getStudent()->getSchoolUnit();
            if (!in_array($unit, $invoiceUnits)) { $invoiceUnits[] = $unit; }
          }
          if (count($invoiceUnits)>1) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nu poți achita facturi de la unități școlare diferite. Te rugăm să efectuezi plăți individuale sau să selectezi doar facturi aferente
                aceleiași unități școlare.'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          if ($thePayment->getPayAmount() >= $invoicesRemaining) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Plata făcută este mai mare sau egală decât suma totală a facturilor. Te rugăm să corectezi suma sau să selectezi Facturi multiple (integral).'
            );
            return $this->redirectToRoute('smart_pay', array('accId' => $accId, 'edit' => $edit));
          }
          //END checks
        }

        $addAdvance = $form->get('addAdvance')->getData();
        if($addAdvance == false) {
          $thePayment->setPayAdvance(0);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($thePayment);
        $entityManager->flush();

        if ($hasPayProof) {
          //START check +add for file
          $files = $form->get('payProof')->getData();
          $unix = time();
          $index = 0;

          foreach($files as $file) {
            $supportedExtensions = array(
              'pdf', 'jpg', 'jpeg', 'png',
            );

            if (!in_array(strtolower($file->guessExtension()),$supportedExtensions)) {
              $this->get('session')->getFlashBag()->add(
                  'notice',
                  'Fișierul '.$file->getClientOriginalName().' nu conține unul din cele 4 formate suportate (PDF, PNG, JPG, JPEG) și nu a fost atașat plății.'
              );
            } else {
              $newProof = new PaymentProof();

              $fileName = 'dovada_factura_'.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber().'_'.$unix.'_'.$index.'.'.$file->guessExtension();

              // Move the file to the directory where brochures are stored
              try {
                $file->move(
                  $this->getParameter('invoice_directory'),
                  $fileName
                );
              } catch (FileException $e) {
                // ... handle exception if something happens during file upload
              }

              // updates the 'pay proof' property to store the PDF file name
              // instead of its contents
              $newProof->setProof($fileName);
              $newProof->setPayment($thePayment);
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($newProof);
              $entityManager->flush();

              $index = $index + 1;
            }
          }
        }
        //END check for file

        if ($this->getUser()->getUsertype() == 'ROLE_PARENT') {
          //Send email to notify - ie. admin@iteachsmart.ro
          $message = (new \Swift_Message('NOTIFICARE Plată nouă - '.$invoice->getMonthAccount()->getStudent()->getUser()->getRoName()))
            ->setFrom('no-reply@iteachsmart.ro')
            // ->setTo('dj.diablo.x@gmail.com')
            ->setTo('georgeta_sotae@yahoo.com')
            ->setCc('nicoleta_sotae@yahoo.com')
            ->setBcc('admin@iteachsmart.ro')
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'home/email.new.smartpay.html.twig',
                    array('payment' => $thePayment)
                ),
                'text/html'
            )
          ;

          $mailer->send($message);
          return $this->redirectToRoute('myaccount_invoices');
        } else {
          return $this->redirectToRoute('account_invoices', array('accId' => $accId));
        }
      }

      return $this->render('accounts/smartpay.html.twig', [
        'month_account' => $monthAccount,
        'balance' => $availableBalance,
        'form' => $form->createView(),
      ]);
    }

    /**
     * @Route("/accounts/smartpay_confirm/{payId}/{accId}/{redirect?'no'}", name="smart_pay_confirm")
     */
    public function smart_pay_confirm(Request $request, $payId, $accId, $redirect, \Swift_Mailer $mailer)
    {
      $payment = $this->getDoctrine()->getRepository
      (Payment::class)->find($payId);

      $totalPaid = $payment->getPayAmount() + $payment->getPayAdvance();
      $invoicePaid = $payment->getPayAmount();
      $invoiceAdvance = $payment->getPayAdvance();

      if ($payment->getPayMethod() == 'single') {
        $invoice = $payment->getPayInvoices()->first();
        $invoice->setInvoicePaid($invoice->getInvoicePaid() + $invoicePaid);
        $invoice->setInvoicePaidDate($payment->getPayDate());
        $invoice->setIsPaid(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($invoice);
        $entityManager->flush();

        $monthAcc = $invoice->getMonthAccount();
        $monthAcc->setTotalPaid($monthAcc->getTotalPaid() + $invoicePaid);
        $monthAcc->setAdvanceBalance($monthAcc->getAdvanceBalance() + $invoiceAdvance);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($monthAcc);
        $entityManager->flush();

        $payment->setIsPending(false);
        $payment->setIsConfirmed(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($payment);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'hurray',
            'Plata a fost CONFIRMATĂ și contul a fost actualizat!'
        );
      }
      if ($payment->getPayMethod() == 'partial') {
        $invoice = $payment->getPayInvoices()->first();
        $invoice->setInvoicePaid($invoice->getInvoicePaid() + $invoicePaid);
        $invoice->setInvoicePaidDate($payment->getPayDate());
        $invoice->setIsPaid(false);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($invoice);
        $entityManager->flush();

        $monthAcc = $invoice->getMonthAccount();
        $monthAcc->setTotalPaid($monthAcc->getTotalPaid() + $invoicePaid);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($monthAcc);
        $entityManager->flush();

        $payment->setIsPending(false);
        $payment->setIsConfirmed(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($payment);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'hurray',
            'Plata a fost CONFIRMATĂ și contul a fost actualizat!'
        );
      }
      if ($payment->getPayMethod() == 'multiple') {

        //fist double check total
        $totalInvRemaining = 0;

        foreach($payment->getPayInvoices() as $invoice) {
          $totalInvRemaining = $totalInvRemaining + $invoice->getInvoiceTotal() - $invoice->getInvoicePaid();
        }
        if ($totalInvRemaining != $payment->getPayAmount()) {
          $this->get('session')->getFlashBag()->add(
              'notice',
              'Plata nu corespunde în mod corect facturilor asociate!!!'
          );
          return $this->redirectToRoute('account_invoices', array('accId' => $accId));
        }

        //the following 2x lines are to distribute advance equally between invoice accounts
        $invoiceCount = $payment->getPayInvoices()->count();
        $individualAdvance = $payment->getPayAdvance() / $invoiceCount;

        foreach($payment->getPayInvoices() as $invoice) {
          $oldInvPaid = $invoice->getInvoicePaid();
          $invoice->setInvoicePaid($invoice->getInvoiceTotal());
          $invoice->setInvoicePaidDate($payment->getPayDate());
          $invoice->setIsPaid(true);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($invoice);
          $entityManager->flush();

          $monthAcc = $invoice->getMonthAccount();
          $monthAcc->setTotalPaid($monthAcc->getTotalPaid() - $oldInvPaid + $invoice->getInvoicePaid());
          $monthAcc->setAdvanceBalance($monthAcc->getAdvanceBalance() + $individualAdvance);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($monthAcc);
          $entityManager->flush();
        }

        $payment->setIsPending(false);
        $payment->setIsConfirmed(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($payment);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'hurray',
            'Plata a fost CONFIRMATĂ și contul a fost actualizat!'
        );
      }
      if ($payment->getPayMethod() == 'multiple_partial') {
        //the following 2x lines are to distribute the sum equally between invoices
        $invoiceCount = $payment->getPayInvoices()->count();
        $individualSum = $payment->getPayAmount() / $invoiceCount;
        $remainingSum = $payment->getPayAmount();

        while ($remainingSum > 0) {
          foreach ($payment->getPayInvoices() as $invoice) {
            if ($remainingSum > 0) {
              $invoiceRemaining = $invoice->getInvoiceTotal() - $invoice->getInvoicePaid();
              $monthAcc = $invoice->getMonthAccount();
              $oldInvPaid = $invoice->getInvoicePaid();
              if ($invoiceRemaining != 0) {
                if ($individualSum < $invoiceRemaining) {
                  $invoice->setInvoicePaid($invoice->getInvoicePaid() + $individualSum);
                  $invoice->setIsPaid(false);
                  $monthAcc->setTotalPaid($monthAcc->getTotalPaid() + $individualSum);
                  $remainingSum = $remainingSum - $individualSum;
                } elseif ($individualSum == $invoiceRemaining) {
                  $invoice->setInvoicePaid($invoice->getInvoicePaid() + $individualSum);
                  $invoice->setIsPaid(true);
                  $monthAcc->setTotalPaid($monthAcc->getTotalPaid() + $individualSum);
                  $remainingSum = $remainingSum - $individualSum;
                } else {
                  $invoice->setInvoicePaid($invoice->getInvoicePaid() + $invoiceRemaining);
                  $invoice->setIsPaid(true);
                  $monthAcc->setTotalPaid($monthAcc->getTotalPaid() + $invoiceRemaining);
                  $remainingSum = $remainingSum - $invoiceRemaining;
                  $invoiceCount = $invoiceCount - 1;
                  //the following check is just as a precaution
                  if ($invoiceCount == 0) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'DEBUG - a existat o problema la calculare - te rugăm să verifici codul PHP!!!'
                    );
                    return $this->redirectToRoute('account_invoices', array('accId' => $accId));
                  }
                  $individualSum = $remainingSum/$invoiceCount;
                }
                $invoice->setInvoicePaidDate($payment->getPayDate());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($invoice);
                $entityManager->flush();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($monthAcc);
                $entityManager->flush();
              }
            }
          }
          $individualSum = $remainingSum/$invoiceCount;
        }
        $payment->setIsPending(false);
        $payment->setIsConfirmed(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($payment);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'hurray',
            'Plata a fost CONFIRMATĂ și contul a fost actualizat!'
        );
      }

      $parentUser = $payment->getPayInvoices()[0]->getMonthAccount()->getStudent()->getUser()->getGuardian()->getUser();

      if ($payment->getIsConfirmed() == true) {
        $message = (new \Swift_Message('Planeta Copiilor - Confirmare plată'))
          ->setFrom('no-reply@iteachsmart.ro')
          ->setTo($parentUser->getEmail())
          ->setBody(
              $this->renderView(
                  'accounts/payment_confirm_email.html.twig',
                  array('payment' => $payment)
              ),
              'text/html'
          )
        ;

        if ($parentUser->getNotifySecond()) {
          $secondaryEmail = $parentUser->getSecondaryEmail();
          $message->setCc($secondaryEmail);
        }

        $mailer->send($message);

        //console.log('A mers!');
        //$response = new Response();
        //$response->send();
      }

      if ($redirect == 'payments') {
        return $this->redirectToRoute('payments');
      } else if ($redirect == 'invoices') {
        return $this->redirectToRoute('invoices');
      } else {
        return $this->redirectToRoute('account_invoices', array('accId' => $accId));
      }

    }

    /**
     * @Route("/accounts/smartpay_deny/{payId}/{accId}/{redirect?'no'}", name="smart_pay_deny")
     */
    public function smart_pay_deny(Request $request, $payId, $accId, $redirect, \Swift_Mailer $mailer)
    {
      $payment = $this->getDoctrine()->getRepository
      (Payment::class)->find($payId);

      $payment->setIsPending(false);
      $payment->setIsConfirmed(false);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($payment);
      $entityManager->flush();

      $this->get('session')->getFlashBag()->add(
          'hurray',
          'Plata a fost RESPINSĂ cu succes!'
      );

      if ($payment->getPaymentProofs()->count() == 0) {
        $reason = "Ordinul de plată nu a fost atașat. Te rugăm să folosești unul din formatele suportate de platformă (PDF, JPG, JPEG, PNG)!";
      } else {
        if ($payment->getPayInvoices()->count() == 1) {
          $reason = "Dovada de plată nu corespunde sumei facturii selectate. Te rugăm să urmezi cu atenție instrucțiunile din platformă.";
        } else {
          $reason = "Dovada de plată nu corespunde sumei facturilor selectate. Te rugăm să urmezi cu atenție instrucțiunile din platformă.";
        }
      }

      $parentUser = $payment->getPayInvoices()[0]->getMonthAccount()->getStudent()->getUser()->getGuardian()->getUser();

      $message = (new \Swift_Message('Planeta Copiilor - Plată RESPINSĂ'))
        ->setFrom('no-reply@iteachsmart.ro')
        ->setTo($parentUser->getEmail())
        ->setBody(
            $this->renderView(
                'accounts/payment_reject_email.html.twig',
                array('payment' => $payment, 'reason' => $reason)
            ),
            'text/html'
        )
      ;

      if ($parentUser->getNotifySecond()) {
        $secondaryEmail = $parentUser->getSecondaryEmail();
        $message->setCc($secondaryEmail);
      }

      $mailer->send($message);

      if ($redirect == 'payments') {
        return $this->redirectToRoute('payments');
      } else if ($redirect == 'invoices') {
        return $this->redirectToRoute('invoices');
      } else {
        return $this->redirectToRoute('account_invoices', array('accId' => $accId));
      }
    }

    /**
     * @Route("/smartpay_proof/{prfId}/{action}", name="smartpay_proof")
     * @Method({"GET", "POST"})
     */
    public function smartpay_proof($prfId, $action)
    {
        $proof = $this->getDoctrine()->getRepository
        (PaymentProof::class)->find($prfId);

        if ($this->getUser()->getUsertype() == 'ROLE_PARENT') {
          $guardian = $proof->getPayment()->getPayInvoices()->first()->getMonthAccount()->getStudent()->getUser()->getGuardian();
          if ($guardian->getUser() != $this->getUser()) {
            return $this->redirectToRoute('myaccount_invoices');
          }
        }

        $fileName = $proof->getProof();
        $filePath = $this->getParameter('invoice_directory').'/'.$fileName;

        if ($action=='download') {
          return $this->file($filePath);
        } else if ($action=='view') {
          return $this->file($filePath, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        } else {
          //do nothing
        }
    }

    /**
     * @Route("/smartpay_prfrem/{prfId}_{accId}", name="smartpay_prfrem")
     * @Method({"GET", "POST"})
     */
    public function smartpay_prfrem($prfId, $accId)
    {
        $proof = $this->getDoctrine()->getRepository
        (PaymentProof::class)->find($prfId);

        if ($this->getUser()->getUsertype() == 'ROLE_PARENT') {
          $guardian = $proof->getPayment()->getPayInvoices()->first()->getMonthAccount()->getStudent()->getUser()->getGuardian();
          if ($guardian->getUser() != $this->getUser()) {
            return $this->redirectToRoute('myaccount_invoices');
          }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($proof);
        $entityManager->flush();

        if ($this->getUser()->getUsertype() == 'ROLE_PARENT') {
          return $this->redirectToRoute('myaccount_invoices');
        } else {
          return $this->redirectToRoute('account_invoices', array('accId' => $accId));
        }

    }

    /**
     * @Route("/accounts/item/add/{monthAccId}/{redirect?'redirect'}", name="accounts_item_add")
     */
    public function accounts_item_add(Request $request, $monthAccId, $redirect)
    {
        $monthAccount = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($monthAccId);

        $student = $monthAccount->getStudent();

        $monthYear = $monthAccount->getAccYearMonth();

        $payItem = new PaymentItem();
        $payItem->setMonthAccount($monthAccount);
        $payItem->setIsEdited(true);

        $form = $this->createForm(PaymentItemType::Class, $payItem);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $payItem = $form->getData();

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($payItem);
          $entityManager->flush();

          //also update the monthAccount total
          $monthAccount->setTotalPrice($monthAccount->getTotalPrice()+$payItem->getItemPrice()*$payItem->getItemCount());
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($monthAccount);
          $entityManager->flush();

          if ($redirect == 'invoicing') {
            return $this->redirectToRoute('account_invoices', array('accId' => $monthAccId));
          } else {
            return $this->redirectToRoute('accounts_stud_month', array(
              'monthYear' => $monthYear->format('Y-m'),
              'studId' => $student->getId(),
            ));
          }
        }

        return $this->render('accounts/accounts.item.add.html.twig', [
          'student' => $student,
          'month_year' => $monthYear,
          'form' => $form->createView()
        ]);
    }

    //TODO Find out how to fix 500 error which occurs in the browser console
    //when a delete statement is executed
    /**
     * @Route("/accounts/item/{itemId}/delete", name="accounts_item_delete")
     * @Method({"DELETE", "POST"})
     */
    public function accounts_item_delete(Request $request, $itemId)
    {
      $payItem = $this->getDoctrine()->getRepository
      (PaymentItem::class)->find($itemId);

      $monthAccount = $payItem->getMonthAccount();
      $removePrice = $payItem->getItemPrice();
      $removeCount = $payItem->getItemCount();

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($payItem);
      $entityManager->flush();

      $monthAccount->setTotalPrice($monthAccount->getTotalPrice() - $removePrice*$removeCount);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($monthAccount);
      $entityManager->flush();

      if ($payItem->getIsInvoiced() == true) {
          $invoice = $payItem->getAccountInvoice();
          $invoiceTotal = $invoice->getInvoiceTotal() - $removePrice*$removeCount;
          if ($invoiceTotal == 0) {
            //TODO implement
            if ($invoice->getTrueAccountInvoice()) {
              $parentInvoice = $invoice->getTrueAccountInvoice();
              $parentInvoice->setTrueInvoice(null);
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($parentInvoice);
              $entityManager->flush();
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($invoice);
            $entityManager->flush();
          } else {
            $invoice->setInvoiceTotal($invoiceTotal);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invoice);
            $entityManager->flush();
          }
      }

      //console.log('A mers!');
      $response = new Response();
      $response->send();

    }

    /**
     * @Route("/accounts/item/{itemId}/{redirect?'redirect'}", name="accounts_item_modify")
     */
    public function accounts_item_modify(Request $request, $itemId, $redirect)
    {

        $payItem = $this->getDoctrine()->getRepository
        (PaymentItem::class)->find($itemId);

        $monthAccount = $payItem->getMonthAccount();
        $student = $monthAccount->getStudent();
        $monthYear = $monthAccount->getAccYearMonth();

        $removePrice = $payItem->getItemPrice();
        $removeCount = $payItem->getItemCount();
        $removeTotal = $removePrice*$removeCount;

        $form = $this->createForm(PaymentItemType::Class, $payItem);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $payItem = $form->getData();
          $payItem->setIsEdited(true);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($payItem);
          $entityManager->flush();

          $newPrice = $payItem->getItemPrice();
          $newCount = $payItem->getItemCount();
          $newTotal = $newPrice*$newCount;

          $monthAccount->setTotalPrice($monthAccount->getTotalPrice() - $removeTotal + $newTotal);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($monthAccount);
          $entityManager->flush();

          if ($payItem->getIsInvoiced() == true) {
              $invoice = $payItem->getAccountInvoice();
              $invoice->setInvoiceTotal($invoice->getInvoiceTotal() - $removeTotal + $newTotal);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($invoice);
              $entityManager->flush();
          }

          if ($redirect == 'invoicing') {
            return $this->redirectToRoute('account_invoices', array('accId' => $monthAccount->getId()));
          } else {
            return $this->redirectToRoute('accounts_stud_month', array(
              'monthYear' => $monthYear->format('Y-m'),
              'studId' => $student->getId(),
            ));
          }
        }

        return $this->render('accounts/accounts.item.modify.html.twig', [
          'pay_item' => $payItem,
          'student' => $student,
          'month_year' => $monthYear,
          'form' => $form->createView()
        ]);
    }


    //DEPRECATED - accounts_pay_invoice is the old PaySystem which should be removed
    /**
     * @Route("/accounts/payinvoice/{invId}", name="accounts_pay_invoice")
     * @Method({"GET" , "DELETE", "POST"})
     */
    public function accounts_pay_invoice(Request $request, $invId)
    {

        $invoice = $this->getDoctrine()->getRepository
        (AccountInvoice::class)->find($invId);

        $oldPaidPrice = $invoice->getInvoicePaid();

        $student = $invoice->getMonthAccount()->getStudent();

        $monthYear = $invoice->getMonthAccount()->getAccYearMonth();

        // $invoice->setPayProof(
        //     new File($this->getParameter('invoice_directory').'/'.$invoice->getPayProof())
        // );

        $invoice->setPayProof(null);

        $form = $this->createForm(AccountInvoiceType::Class, $invoice);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $invoice = $form->getData();

          $invoice->setIsPaid(true);
          $invoice->setInvoicePaidDate(new \DateTime('now'));

          // $file stores the uploaded PDF file
          /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
          $file = $form->get('payProof')->getData();

          $fileName = 'dovada_factura_'.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber().'.'.$file->guessExtension();

          // Move the file to the directory where brochures are stored
          try {
              $file->move(
                  $this->getParameter('invoice_directory'),
                  $fileName
              );
          } catch (FileException $e) {
              // ... handle exception if something happens during file upload
          }

          // updates the 'pay proof' property to store the PDF file name
          // instead of its contents
          $invoice->setPayProof($fileName);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($invoice);
          $entityManager->flush();

          $account = $invoice->getMonthAccount();
          $account->setTotalPaid($account->getTotalPaid() - $oldPaidPrice + $invoice->getInvoicePaid());

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($account);
          $entityManager->flush();

          return $this->redirectToRoute('account_invoices', array(
            'accId' => $invoice->getMonthAccount()->getId(),
          ));
        }

        return $this->render('accounts/account.pay.invoice.html.twig', [
          'student' => $student,
          'month_year' => $monthYear,
          'invoice' => $invoice,
          'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/accounts/invoice_proof/{invId}/{action}", name="invoice_proof")
     * @Method({"GET", "POST"})
     */
    public function viewOrDownloadAction($invId, $action)
    {
        $invoice = $this->getDoctrine()->getRepository
        (AccountInvoice::class)->find($invId);

        $fileName = $invoice->getPayProof();
        $filePath = $this->getParameter('invoice_directory').'/'.$fileName;

        if ($action=='download') {
          return $this->file($filePath);
        } else if ($action=='view') {
          return $this->file($filePath, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        } else {

        }
    }

    /**
     * @Route("/accounts/invoice_pdf/{invId}", name="invoice_pdf")
     * @Method({"GET", "POST"})
     */
    public function invoice_pdf(Request $request, $invId)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      $snappy = $this->get('knp_snappy.pdf');
      $html = $this->renderView('accounts/invoice_pdf.html.twig',
        array(
            'invoice'  => $invoice
        ));

      $fileName = 'factura_'.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber().'.pdf';

      // save PDF to server
      // $snappy->generateFromHtml(
      //     $html,
      //     $this->getParameter('pdf_directory').'/'.$fileName
      // );

      return new Response(
        $snappy->getOutputFromHtml($html),
        //ok status code
        200,
        array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$fileName.'"'
        )
      );
      //console.log('A mers!');
      // $response = new Response();
      // $response->send();

      #return $this->redirectToRoute('users');
    }

    /**
     * @Route("/accounts/combo_pdf/{recId}", name="combo_pdf")
     * @Method({"GET"})
     */
    public function combo_pdf(Request $request, $recId)
    {
      $receipt = $this->getDoctrine()->getRepository
      (AccountReceipt::class)->find($recId);

      $invoice = $receipt->getAccountInvoice();

      $snappy = $this->get('knp_snappy.pdf');
      $html = $this->renderView('accounts/combo_pdf.html.twig',
        array(
            'invoice'  => $invoice,
            'receipt'  => $receipt,
        ));

      $fileName = 'factura_'.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber().'.pdf';

      return new Response(
        $snappy->getOutputFromHtml($html),
        //ok status code
        200,
        array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$fileName.'"'
        )
      );
    }

    /**
     * @Route("/smart_receipt_pdf/{recId}", name="smart_receipt_pdf")
     * @Method({"GET"})
     */
    public function smart_receipt_pdf(Request $request, $recId)
    {
      $receipt = $this->getDoctrine()->getRepository
      (SmartReceipt::class)->find($recId);

      if ($this->getUser()->getUsertype() == 'ROLE_PARENT') {
        if ($receipt->getPayment()->getPayInvoices()[0]->getMonthAccount()->getStudent()->getUser()->getGuardian()->getUser() != $this->getUser()) {
          return $this->redirectToRoute('myaccount_invoices');
        }
      }

      $students = array();
      foreach ($receipt->getPayment()->getPayInvoices() as $invoice) {
        if (!in_array($invoice->getMonthAccount()->getStudent(), $students)) { $students[] = $invoice->getMonthAccount()->getStudent(); }
      }

      $snappy = $this->get('knp_snappy.pdf');
      $html = $this->renderView('accounts/smart_receipt_pdf.html.twig',
        array(
            'receipt'  => $receipt,
            'payment'  => $receipt->getPayment(),
            'students' => $students,
        ));

      $fileName = 'chitanta_'.$receipt->getReceiptSerial().'-'.$receipt->getReceiptNumber().'.pdf';

      // save PDF to server
      // $snappy->generateFromHtml(
      //     $html,
      //     $this->getParameter('pdf_directory').'/'.$fileName
      // );

      return new Response(
        $snappy->getOutputFromHtml($html, array(
          'orientation' => 'Landscape',
          'page-height' => 220,
          'page-width'  => 140,
          'dpi' => 300,
        )),
        //ok status code
        200,
        array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$fileName.'"'
        )
      );
    }

    /**
     * @Route("/accounts/receipt_pdf/{recId}", name="receipt_pdf")
     * @Method({"GET"})
     */
    public function receipt_pdf(Request $request, $recId)
    {
      $receipt = $this->getDoctrine()->getRepository
      (AccountReceipt::class)->find($recId);

      $snappy = $this->get('knp_snappy.pdf');
      $html = $this->renderView('accounts/receipt_pdf.html.twig',
        array(
            'receipt'  => $receipt,
            'invoice'  => $receipt->getAccountInvoice(),
        ));

      $fileName = 'chitanta_'.$receipt->getReceiptSerial().'-'.$receipt->getReceiptNumber().'.pdf';

      // save PDF to server
      // $snappy->generateFromHtml(
      //     $html,
      //     $this->getParameter('pdf_directory').'/'.$fileName
      // );

      return new Response(
        $snappy->getOutputFromHtml($html, array(
          'orientation' => 'Landscape',
          'page-height' => 220,
          'page-width'  => 140,
          'dpi' => 300,
        )),
        //ok status code
        200,
        array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$fileName.'"'
        )
      );
    }

    /**
     * @Route("/accounts/invoice_lock/{invId}", name="invoice_lock")
     * @Method({"GET", "POST"})
     */
    public function invoice_lock(Request $request, $invId)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      $invoice->setIsLocked(true);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->flush();

      return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
    }

    /**
     * @Route("/accounts/invoice_unlock/{invId}", name="invoice_unlock")
     * @Method({"GET", "POST"})
     */
    public function invoice_unlock(Request $request, $invId)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      $invoice->setIsLocked(false);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->flush();

      return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
    }

    /**
     * @Route("/accounts/invoice_from_proforma/{invId}", name="invoice_from_proforma")
     * @Method({"GET", "POST"})
     */
    public function invoice_from_proforma(Request $request, $invId)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      if ($invoice->getIsLocked()) {
        $this->get('session')->getFlashBag()->add(
            'notice',
            'ATENȚIE: Factura '.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber().' este deja salvată!'
        );
        return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
      }

      $this->get('session')->getFlashBag()->add(
          'notice',
          'ATENȚIE: Această funcționalitate este temporar suspendată. Vă rugăm adresați-va echipei de programare pentru mai multe informații!'
      );
      return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));

      $iserial = $invoice->getMonthAccount()->getStudent()->getSchoolUnit()->getFirstInvoiceSerial();

      $newInvoice = new AccountInvoice();

      $newInvoice->setMonthAccount($invoice->getMonthAccount());
      $newInvoice->setInvoiceDate(new \DateTime('now'));
      $newInvoice->setTrueAccountInvoice($invoice);
      $newInvoice->setIsLocked(false);

      /* INVOICE NUMBER LOGIC STARTS HERE */
      $latestInvoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->findLatestBySerial($iserial);

      if ($latestInvoice) {
        $newNumber = $latestInvoice->getInvoiceNumber()+1;
      } else {
        $newNumber = $invoice->getMonthAccount()->getStudent()->getSchoolUnit()->getFirstInvoiceNumber();
      }

      $newInvoice->setInvoiceSerial($iserial);
      $newInvoice->setInvoiceNumber($newNumber);
      $newInvoice->setInvoiceTotal(0);

      $newInvoice->setInvoiceName('Factură Fiscală Nr: '.$iserial.'-'.$newNumber);
      /* INVOICE NUMBER LOGIC ENDS HERE */

      /* PAYEE DETAILS LOGIC STARTS HERE*/
      $gUser = $invoice->getMonthAccount()->getStudent()->getUser()->getGuardian()->getUser();
      if ($gUser->getCustomInvoicing()) {
        $newInvoice->setPayeeIsCompany($gUser->getIsCompany());
        $newInvoice->setPayeeName($gUser->getInvoicingName());
        $newInvoice->setPayeeAddress($gUser->getInvoicingAddress());
        $newInvoice->setPayeeIdent($gUser->getInvoicingIdent());
        $newInvoice->setPayeeCompanyReg($gUser->getInvoicingCompanyReg());
        $newInvoice->setPayeeCompanyFiscal($gUser->getInvoicingCompanyFiscal());
      } else {
        $newInvoice->setPayeeName($gUser->getRoName());
      }
      /* PAYEE DETAILS LOGIC ENDS HERE*/

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($newInvoice);
      $entityManager->flush();

      $total = 0;

      foreach ($invoice->getPaymentItems() as $payItem) {
          $newItem = new PaymentItem();
          $newItem->setMonthAccount($payItem->getMonthAccount());
          $newItem->setItemName($payItem->getItemName().' (***)');
          $newItem->setItemCount($payItem->getItemCount());
          $newItem->setItemPrice($payItem->getItemPrice());
          $newItem->setItemOptional($payItem->getItemOptional());
          $newItem->setIsEdited($payItem->getIsEdited());
          $newItem->setEditNote($payItem->getEditNote());
          $newItem->setAccountInvoice($newInvoice);
          $newItem->setIsInvoiced(true);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($newItem);
          $entityManager->flush();

          $newInvoice->addPaymentItem($newItem);
          $total = $total + $newItem->getItemPrice() * $newItem->getItemCount();
          $newInvoice->setInvoiceTotal($total);
      }

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($newInvoice);
      $entityManager->flush();

      $invoice->setTrueInvoice($newInvoice);
      $invoice->setIsLocked(true);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($invoice);
      $entityManager->flush();

      return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
    }

    /**
     * @Route("/accounts/invoice_to_proforma/{invId}", name="invoice_to_proforma")
     * @Method({"GET", "POST"})
     */
    public function invoice_to_proforma(Request $request, $invId)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      $invoice->setIsProforma('true');
      $invoice->setInvoiceSerial('PRFM');

      $latestProf = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->findLatestBySerial('PRFM');

      if($latestProf) {
        $invoice->setInvoiceNumber($latestProf->getInvoiceNumber()+1);
      } else {
        $invoice->setInvoiceNumber(100);
      }

      $invoice->setInvoiceName('Factură Proforma Nr: '.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber());

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->flush();

      return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
    }

    /**
     * @Route("/accounts/invoice_notify/{invId}", name="invoice_notify")
     * @Method({"GET", "POST"})
     */
    public function invoice_notify(Request $request, $invId, \Swift_Mailer $mailer)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      $invoice->setInvoiceSentDate(new \DateTime('now'));
      $invoice->setSentCount($invoice->getSentCount()+1);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->flush();

      if ($invoice->getSentCount() == 1) {
        $messageTitle = 'Factură Nouă Emisă';
      } else {
        $messageTitle = 'Reminder Factură Emisă';
      }

      $parentUser = $invoice->getMonthAccount()->getStudent()->getUser()->getGuardian()->getUser();


      $message = (new \Swift_Message($messageTitle.' - Planeta Copiilor'))
        ->setFrom('no-reply@iteachsmart.ro')
        ->setTo($parentUser->getEmail())
        ->setBody(
            $this->renderView(
                'accounts/invoice_email.html.twig',
                array('invoice' => $invoice)
            ),
            'text/html'
        )
      ;

      if ($parentUser->getNotifySecond()) {
        $secondaryEmail = $parentUser->getSecondaryEmail();
        $message->setCc($secondaryEmail);
      }

      $mailer->send($message);

      //console.log('A mers!');
      $response = new Response();
      $response->send();

      #return $this->redirectToRoute('users');
    }

    /**
     * @Route("/accounts/stud/{studId}", name="accounts_stud")
     * @Method({"GET" , "POST"})
     */
     public function accounts_stud($studId)
     {
        $accounts = $this->getDoctrine()->getRepository
        (MonthAccount::class)->findByStudent($studId);

        $student = $this->getDoctrine()->getRepository
        (Student::class)->find($studId);

        return $this->render('accounts/accounts.stud.html.twig', [
            'student' => $student,
            'all_accounts' => $accounts,
        ]);
     }

    /**
     * @Route("/accounts/{accId}/reset", name="accounts_stud_month_reset")
     * @Method({"GET" , "DELETE", "POST"})
     */
    public function accounts_stud_month_reset($accId)
    {
        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($accId);

        $student = $account->getStudent();

        if (!empty($account)) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($account);
            $entityManager->flush();

            //console.log('A mers!');
            $response = new Response();
            $response->send();

        } else {
            $response = new Response();
            $response->send();
        }
    }

    /**
     * @Route("/accounts/{itemId}/un_invoice", name="remove_item_from_invoice")
     * @Method({"GET" , "POST"})
     */
    public function remove_item_from_invoice($itemId)
    {
        $payItem = $this->getDoctrine()->getRepository
        (PaymentItem::class)->find($itemId);

        $invoice = $payItem->getAccountInvoice();

        $invoice->removePaymentItem($payItem);
        $invoice->setInvoiceTotal($invoice->getInvoiceTotal() - $payItem->getItemPrice() * $payItem->getItemCount());

        if (count($invoice->getPaymentItems()) == 0) {
            if ($invoice->getTrueAccountInvoice()) {
              $parentInvoice = $invoice->getTrueAccountInvoice();
              $parentInvoice->setTrueInvoice(null);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($parentInvoice);
              $entityManager->flush();
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($invoice);
            $entityManager->flush();
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        //if the item is a duplication from proforma, remove alltogether
        if (strpos($payItem->getItemName(), '(***)') !== false) {
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->remove($payItem);
          $entityManager->flush();
        } else {
          $payItem->setIsInvoiced(false);
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($payItem);
          $entityManager->flush();
        }

        return $this->redirectToRoute('account_invoices', array(
          'accId' => $invoice->getMonthAccount()->getId(),
        ) );
    }

    /**
     * @Route("/accounts/{accId}/invoices", name="account_invoices")
     * @Method({"GET", "POST"})
     */
    public function account_invoices($accId, Request $request)
    {
        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($accId);

        $student = $account->getStudent();
        $invoices = $account->getAccountInvoices();

        $forms = array(); //required in case there are no forms available
        $forms2 = array();
        $forms3 = array();
        $forms4 = array();

        $views = array(); //required in case there are no views available
        $views2 = array();
        $views3 = array();
        $views4 = array();

        foreach ($invoices as $invoice) {

          $form = $this->createForm(AccountInvoiceNumberType::Class, $invoice);

          $forms[] = $form;
          $views[] = $form->createView();

          //next prepare the receipt forms
          $newReceipt = new AccountReceipt();

          $newReceipt->setAccountInvoice($invoice);
          $newReceipt->setReceiptDate(new \DateTime('now'));

          /* RECEIPT VARIABLES */
          $theUnit = $account->getStudent()->getSchoolUnit();
          $iserial = $theUnit->getFirstReceiptSerial();
          $inumber = $theUnit->getFirstReceiptNumber();

          /* RECEIPT NUMBER LOGIC STARTS HERE */
          $latestReceipt = $this->getDoctrine()->getRepository
          (AccountReceipt::class)->findLatestBySerial($iserial);

          if ($latestReceipt == null) {
            $newReceipt->setReceiptSerial($iserial);
            $newReceipt->setReceiptNumber($inumber);
          } else {
            $newNumber = $latestReceipt->getReceiptNumber()+1;
            $newReceipt->setReceiptSerial($iserial);
            $newReceipt->setReceiptNumber($newNumber);
          }
          /* RECEIPT NUMBER LOGIC ENDS HERE */

          $form2 = $this->createForm(AccountInvoiceReceiptType::Class, $newReceipt, array(
            'total' => $invoice->getInvoiceTotal(),
          ));
          $forms2[] = $form2;
          $views2[] = $form2->createView();

          //next prepare the smart receipt forms and smart proof forms
          foreach ($invoice->getPayments() as $payment) {
            if ($payment->getIsConfirmed() == true && $payment->getSmartReceipt() == null ) {
              $newSmartRec = new SmartReceipt();

              $newSmartRec->setPayment($payment);
              $newSmartRec->setReceiptDate(new \DateTime('now'));

              $latestSmartRec = $this->getDoctrine()->getRepository
              (SmartReceipt::class)->findLatestBySerial($iserial);

              if ($latestSmartRec == null) {
                if ($latestReceipt == null) {
                  $newSmartRec->setReceiptSerial($iserial);
                  $newSmartRec->setReceiptNumber($inumber);
                } else {
                  // This latestReceipt scenario should ONLY HAPPEN 1x time
                  // (when switching from old system to the new one)!!
                  $newNumber = $latestReceipt->getReceiptNumber()+1;
                  $newSmartRec->setReceiptSerial($iserial);
                  $newSmartRec->setReceiptNumber($newNumber);
                }
              } else {
                $newNumber = $latestSmartRec->getReceiptNumber()+1;
                $newSmartRec->setReceiptSerial($iserial);
                $newSmartRec->setReceiptNumber($newNumber);
              }

              $form3 = $this->createForm(AccountSmartReceiptType::Class, $newSmartRec, array(
                'total' => $payment->getPayAmount()+$payment->getPayAdvance(),
              ));
              $forms3[] = $form3;
              $views3[] = $form3->createView();
            } elseif ($payment->getIsPending() == true) {
                //TODO REMOVE THIS ELSEIF after tests
            }

            $form4 = $this->createForm(UserMyaccountSmartProofType::Class, $payment);
            $forms4[] = $form4;
            $views4[] = $form4->createView();

          }

        }

        if ($request->isMethod('POST')) {

            foreach ($forms as $form) {
              $form->handleRequest($request);
            }

            foreach ($forms as $form) {
              if ($form->isSubmitted()) {
                if ($form->isValid()) {
                  $invoice = $form->getData();

                  $invoice->setInvoiceSerial(strtoupper($invoice->getInvoiceSerial()));

                  if ($invoice->getIsProforma()) {
                    $invoice->setInvoiceName('Factură Proforma Nr: '.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber());
                  } else {
                    $invoice->setInvoiceName('Factură Fiscală Nr: '.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber());
                  }

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->flush();

                  return $this->redirectToRoute('account_invoices', array('accId' => $accId));
                } else {
                  $this->get('session')->getFlashBag()->add(
                      'notice',
                      //(string) $form->getErrors(true, false)
                      $form->getErrors(true)
                  );
                }
              }
            }

            foreach ($forms2 as $form2) {
              $form2->handleRequest($request);
            }

            foreach ($forms2 as $form2) {
              if ($form2->isSubmitted()) {
                if ($form2->isValid()) {
                  $newReceipt = $form2->getData();

                  // Retrieve the value from the extra, non-mapped field!
                  $receiptTotalPaid = $form2->get('totalPaid')->getData();

                  $invoice = $newReceipt->getAccountInvoice();

                  if($receiptTotalPaid <= 0) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'ATENȚIE: Chitanța NU poate fi generată decat dacă suma plătită este mai mare de 0 RON!'
                    );
                    return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
                  } elseif ($receiptTotalPaid > $invoice->getInvoiceTotal()) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'ATENȚIE: Chitanța NU poate fi generată pe o sumă mai mare decât cea înscrisă în factură!'
                    );
                    return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
                  }

                  $newReceipt->setReceiptSerial(strtoupper($newReceipt->getReceiptSerial()));

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($newReceipt);
                  $entityManager->flush();

                  $oldInvoicePaid = $invoice->getInvoicePaid();
                  $invoice->setIsLocked(true);
                  //when generating a receipt, set invoice to isPaid and update totalPaid
                  $invoice->setIsPaid(true);
                  $invoice->setInvoicePaid($receiptTotalPaid);
                  $invoice->setInvoicePaidDate($newReceipt->getReceiptDate());

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($invoice);
                  $entityManager->flush();

                  $account->setTotalPaid($account->getTotalPaid() - $oldInvoicePaid + $receiptTotalPaid);

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($account);
                  $entityManager->flush();

                  return $this->redirectToRoute('account_invoices', array('accId' => $accId));
                } else {
                  $this->get('session')->getFlashBag()->add(
                      'notice',
                      //(string) $form->getErrors(true, false)
                      $form2->getErrors(true)
                  );
                }
              }
            }

            foreach ($forms3 as $form3) {
              $form3->handleRequest($request);
            }

            foreach ($forms3 as $form3) {
              if ($form3->isSubmitted()) {
                if ($form3->isValid()) {
                  $newSmartReceipt = $form3->getData();

                  $newSmartReceipt->setReceiptSerial(strtoupper($newSmartReceipt->getReceiptSerial()));

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($newSmartReceipt);
                  $entityManager->flush();

                  return $this->redirectToRoute('account_invoices', array('accId' => $accId));
                } else {
                  $this->get('session')->getFlashBag()->add(
                      'notice',
                      //(string) $form->getErrors(true, false)
                      $form3->getErrors(true)
                  );
                }
              }
            }

            foreach ($forms4 as $form4) {
              $form4->handleRequest($request);
            }

            foreach ($forms4 as $form4) {
              if ($form4->isSubmitted()) {
                if ($form4->isValid()) {

                  $thePayment = $form4->getData();
                  $invoice = $thePayment->getPayInvoices()->first();

                  $files = $form4->get('payProof')->getData();
                  $unix = time();
                  $index = 0;

                  foreach($files as $file) {
                    $supportedExtensions = array(
                      'pdf', 'jpg', 'jpeg', 'png',
                    );

                    if (!in_array(strtolower($file->guessExtension()),$supportedExtensions)) {
                      $this->get('session')->getFlashBag()->add(
                          'notice',
                          'Fișierul '.$file->getClientOriginalName().' nu conține unul din cele 4 formate suportate (PDF, PNG, JPG, JPEG) și nu a fost atașat plății.'
                      );
                    } else {
                      $newProof = new PaymentProof();

                      $fileName = 'dovada_factura_'.$invoice->getInvoiceSerial().'-'.$invoice->getInvoiceNumber().'_'.$unix.'_'.$index.'.'.$file->guessExtension();

                      // Move the file to the directory where brochures are stored
                      try {
                        $file->move(
                          $this->getParameter('invoice_directory'),
                          $fileName
                        );
                      } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                      }

                      // updates the 'pay proof' property to store the PDF file name
                      // instead of its contents
                      $newProof->setProof($fileName);
                      $newProof->setPayment($thePayment);
                      $entityManager = $this->getDoctrine()->getManager();
                      $entityManager->persist($newProof);
                      $entityManager->flush();

                      $index = $index + 1;
                    }
                  }

                  return $this->redirectToRoute('account_invoices', array('accId' => $accId));
                } else {
                  $this->get('session')->getFlashBag()->add(
                      'notice',
                      //(string) $form->getErrors(true, false)
                      $form4->getErrors(true)
                  );
                }
              }
            }


          }

        return $this->render('accounts/account.invoices.html.twig', [
          'student' => $student,
          'account' => $account,
          'month_year' => $account->getAccYearMonth(),
          'payment_items' => $account->getPaymentItems(),
          'invoices' => $invoices,
          'forms' => $views,
          'forms2' => $views2,
          'forms3' => $views3,
          'forms4' => $views4,
        ]);
    }

    /**
     * @Route("/accounts/{accId}/invoiceall/{type?'true'}", name="account_invoice_all")
     * @Method({"GET","POST"})
     */
    public function account_invoice_all($accId, $type)
    {
        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($accId);

        $newInvoice = new AccountInvoice();
        $newInvoice->setMonthAccount($account);
        $newInvoice->setInvoiceDate(new \DateTime('now'));

        /* INVOICE NUMBER LOGIC STARTS HERE */
        $theUnit = $account->getStudent()->getSchoolUnit();

        if ($type == 'proforma') {
          $newInvoice->setIsProforma(true);
          $iserial = 'PRFM';
          $inumber = 100;
          $ititle = 'Factură Proforma Nr: ';
        } else {
          $iserial = $theUnit->getFirstInvoiceSerial();
          $inumber = $theUnit->getFirstInvoiceNumber();
          $ititle = 'Factură Fiscală Nr: ';
        }

        $latestInvoice = $this->getDoctrine()->getRepository
        (AccountInvoice::class)->findLatestBySerial($iserial);

        if ($latestInvoice == null) {

          $newInvoice->setInvoiceSerial($iserial);
          $newInvoice->setInvoiceNumber($inumber);

          $newInvoice->setInvoiceName($ititle.$iserial.'-'.$inumber);
        } else {
          $newNumber = $latestInvoice->getInvoiceNumber()+1;
          $newInvoice->setInvoiceSerial($iserial);
          $newInvoice->setInvoiceNumber($newNumber);

          $newInvoice->setInvoiceName($ititle.$iserial.'-'.$newNumber);
        }
        /* INVOICE NUMBER LOGIC ENDS HERE */

        /* PAYEE DETAILS LOGIC STARTS HERE*/
        $gUser = $account->getStudent()->getUser()->getGuardian()->getUser();
        if ($gUser->getCustomInvoicing()) {
          $newInvoice->setPayeeIsCompany($gUser->getIsCompany());
          $newInvoice->setPayeeName($gUser->getInvoicingName());
          $newInvoice->setPayeeAddress($gUser->getInvoicingAddress());
          $newInvoice->setPayeeIdent($gUser->getInvoicingIdent());
          $newInvoice->setPayeeCompanyReg($gUser->getInvoicingCompanyReg());
          $newInvoice->setPayeeCompanyFiscal($gUser->getInvoicingCompanyFiscal());
        } else {
          $newInvoice->setPayeeName($gUser->getRoName());
        }
        /* PAYEE DETAILS LOGIC ENDS HERE*/

        $total = 0;

        foreach ($account->getPaymentItems() as $payItem) {
            if ($payItem->getIsInvoiced() == false) {
                $payItem->setIsInvoiced(true);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($payItem);
                $entityManager->flush();

                $newInvoice->addPaymentItem($payItem);
                $total = $total + $payItem->getItemPrice() * $payItem->getItemCount();
                $newInvoice->setInvoiceTotal($total);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($newInvoice);
        $entityManager->flush();

        return $this->redirectToRoute('account_invoices', array(
          'accId' => $account->getId(),
        ) );
    }

    /**
     * @Route("/accounts/{accId}/invoiceselect/", name="account_invoice_selected")
     * @Method({"GET","POST"})
     */
    public function account_invoice_selected($accId)
    {
        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($accId);

        $newInvoice = new AccountInvoice();
        $newInvoice->setMonthAccount($account);
        $newInvoice->setInvoiceDate(new \DateTime('now'));
        $newInvoice->setInvoiceName('Factură Fiscală Nr: 0');

        $total = 0;

        foreach ($account->getPaymentItems() as $payItem) {
          //TODO: if payment item is in array <- get array from javascript
            if ($payItem->getIsInvoiced() == false) {
                $payItem->setIsInvoiced(true);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($payItem);
                $entityManager->flush();

                $newInvoice->addPaymentItem($payItem);
                $total = $total + $payItem->getItemPrice() * $payItem->getItemCount();
                $newInvoice->setInvoiceTotal($total);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($newInvoice);
        $entityManager->flush();

        //TODO Change invoice number logic + ADD personal details logic (use comments)
        $newInvoice->setInvoiceName('Factură Fiscală Nr: '.$newInvoice->getId());
        //END TODO
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('account_invoices', array(
          'accId' => $account->getId(),
        ) );
    }

    /**
     * @Route("/accounts/smart_generate", name="smart_generate")
     * @Method({"GET" , "POST"})
     */
    public function smart_generate(Request $request)
    {

      $doc = $this->getDoctrine();
      $currSchooYearRepo = $doc->getRepository(SchoolYear::class);
      $currentSchoolYear = $currSchooYearRepo->findCurrentYear();
      $mY = new \DateTime('now'); //now

      $firstMonth = $currentSchoolYear->getStartDate()->modify('first day of this month');
      $lastMonth = $currentSchoolYear->getEndDate()->modify('last day of this month');
      
      $monthChoices = array();
      $month = $firstMonth; //used to iterate; use '= clone' instead of '=' for dates!
      while ($month < $lastMonth) {
        $monthChoices[] = clone $month;
        $month->modify('last day of this month');
        $month->modify('+ 1 day');
      }

      $form = $this->createForm(SmartGenerateType::Class, $data = null, array(
        'month_choices' => $monthChoices,
        'year' => $currentSchoolYear->getId()
      ));

      $view = $form->createView();

      return $this->render('accounts/smart_generate.html.twig', ['form' => $view]);
    }

    /**
    * @Route("/accounts/generate_invoices", name="generate_invoices")
    * @return JsonResponse
    */
    public function generate_invoices(Request $request) 
    {
        $doc = $this->getDoctrine();
        $em = $doc->getManager();
        $currSchooYearRepo = $doc->getRepository(SchoolYear::class);
        $currentSchoolYear = $currSchooYearRepo->findCurrentYear();

        $mY = new \DateTime('now'); //now

        $firstMonth = $currentSchoolYear->getStartDate()->modify('first day of this month');
        $lastMonth = $currentSchoolYear->getEndDate()->modify('last day of this month');
        
        $monthChoices = array();
        $month = $firstMonth; //used to iterate; use '= clone' instead of '=' for dates!
        while ($month < $lastMonth) {
          $monthChoices[] = clone $month;
          $month->modify('last day of this month');
          $month->modify('+ 1 day');
        }

      $form = $this->createForm(SmartGenerateType::Class, $data = null, array(
        'month_choices' => $monthChoices,
        'year' => $currentSchoolYear->getId()
      ));

      $form->handleRequest($request);
      $formView = $form->createView();

      if($form->isSubmitted() && $form->isValid()) {

        $data = $form->getData();
        $dataErrors = $this->dataValidation($data, $formView);
        
        if ($dataErrors) {
          $view = $this->renderView('accounts/smart_generate.html.twig', ['form' => $formView]);
          return new JsonResponse(['view' => $view]);
        }
        
        $selectedStudents = array();
        $index            = 0;
        $studentRepo      = $doc->getRepository(Student::class);

        if ($data['stud_choice'] == 'all') {
          $selectedStudents = $studentRepo->findAll();
        } elseif ($data['stud_choice'] == 'specific') {
          $selectedStudents = $data['students'];
        } elseif ($data['stud_choice'] == 'excluding') {
          $selectedStudents = $studentRepo->findAllStudentsExcept($data['students']->toArray());
        }


        //======================================
        //====== start foreach =================
        //======================================

        // Starting summary string
        $summary = "--------------------\nAi selectat ".count($selectedStudents).' x Elevi!'."\n--------------------\n";

        
        $monthAccountRepo        = $doc->getRepository(MonthAccount::class);
        $optionalsAttendanceRepo = $doc->getRepository(OptionalsAttendance::class);
        $transportTripRepo       = $doc->getRepository(TransportTrip::class);
        $accountInvoiceRepo      = $doc->getRepository(AccountInvoice::class);


        //NEW SCRIPT FOR CALCULATING THE ADVANCE BALANCE
        $monthYear = $data['year_month']->format('Y-m-d H:i:s');
        $monthAccountRepo->calculateAdvaceBalance($monthYear);



        ///!!!!!!!!!!!!!!!!!!!!!!!!
        if (in_array('tax', $data['pay_item_type'])) {
          //$this->generateServiceTax($selectedStudents, $data['year_month']);
        }

        if (in_array('optionals', $data['pay_item_type'])) {
          $this->generateOptionals($selectedStudents, $data);
        }

        foreach($selectedStudents as $student) {
          $allCreatedItems = array();  // set array for 1x INVOICE
          $advanceRemaining = 0; // set variable to handle Advance
        } //finishes foreach student


        $this->get('session')->getFlashBag()->add('notice', "SUMAR: \n".$summary);

        $view = $this->renderView('accounts/smart_generate.html.twig', ['form' => $formView]);
        return new JsonResponse(['view' => $view]);

      } else {
        $view = $this->renderView('accounts/smart_generate.html.twig', ['form' => $formView]);
        return new JsonResponse(['view' => $view]);
        return new JsonResponse([
          'view' => $this->renderView('accounts/smart_generate.html.twig', [
              'view' => $formView
            ])
        ]);
      }
    }

    /**
     * Loops through the selected students and creates new month accounts for each student that doesn't have one
     *
     * @param array    $selectedStudent Array of student objects
     * @param string   $summary         String containing the summary
     * @param array    $data            Array containing the request data
     *
     * @return string
     */
    protected function generateMonthAccounts($selectedStudents, $summary, $data){
      $doc = $this->getDoctrine();
      $em = $doc->getManager();
      $monthYear = $data['year_month'];
      $monthAccountRepo = $doc->getRepository(MonthAccount::class);
      
      //in order to process faster insertion data we have to use batch processing 
      //https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/batch-processing.html
      $batchSize = 100; 
      $nbrNewStudents = 0;
      
      $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
      $formatter->setPattern('MMMM, YYYY');

      foreach($selectedStudents as $student) {
        $searchAccount = $monthAccountRepo->findBy([
          'student' => $student, 
          'accYearMonth' => $monthYear
        ]);

        $nbrNewStudents++;
        if (!$searchAccount) { //if account exists, use it, otherwise create one
          $account = new MonthAccount();
          $account->setStudent($student);
          $account->setAccYearMonth($monthYear);
          $em->persist($account);
        } else {
          $account = $searchAccount[0];
        }

        if ($nbrNewStudents % $batchSize === 0) {
          $em->flush();
          $em->clear(MonthAccount::class);
          $em->clear(PaymentItem::class);
        }
      }

      $em->flush(); 
      $em->clear(MonthAccount::class);
      $em->clear(PaymentItem::class);

      return $summary;
    }

    /**
     * @param array        $selectedStudents    Array of student objects
     * @param DateTime     $monthYear  Object containing current month
     *
     * @return string
     */
    protected function generateServiceTax($selectedStudents, $monthYear) {
      $doc              = $this->getDoctrine();
      $em               = $doc->getManager();
      $monthAccountRepo = $doc->getRepository(MonthAccount::class);

      //in order to process faster insertion data we have to use batch processing 
      //https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/batch-processing.html
      $batchSize = 100; 
      $nbrNewStudents = 0;

      $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
      $formatter->setPattern('MMMM');

      foreach ($selectedStudents as $student) {
          $nbrNewStudents++;
          $schoolService = $student->getEnrollment()->getIdService();
          $displayMonth = clone $monthYear;

          if ($schoolService->getInAdvance() == false) {
              $displayMonth->modify('-1 day');
          }

          $account = $monthAccountRepo->findBy([
              'student' => $student, 
              'accYearMonth' => $monthYear
          ]);

          if ($account) {
              $account = $account[0];
          } else {
              continue;
          }

          $serviceTaxItem = new PaymentItem();
          $serviceTaxItem->setMonthAccount($account);
          $serviceTaxItem->setItemName($schoolService->getServicename().' '.strtoupper($formatter->format($displayMonth)));
          $serviceTaxItem->setItemPrice($schoolService->getServiceprice());

          $em->persist($serviceTaxItem);

          $account->addPaymentItem($serviceTaxItem);
          $account->addToTotalPrice($serviceTaxItem->getItemPrice());

          $em->persist($account);

          if ($nbrNewStudents % $batchSize === 0) {
              $em->flush();
              $em->clear(MonthAccount::class);
              $em->clear(PaymentItem::class);
          }

          $advanceBalance = $monthAccountRepo->getAdvanceBalanceByStudent($student->getId());
      }

      $em->flush();
      $em->clear(MonthAccount::class);
      $em->clear(PaymentItem::class);

       // END School Service Tax
      $allCreatedItems[] = $serviceTaxItem;

      return '';
    }

    /**
     * @param array $selectedStudents Array of student objects
     * @param array $data             Object containing current month
     *
     * @return string
     */
    protected function generateOptionals($selectedStudents, $data) {
      $doc = $this->getDoctrine();
      $em = $doc->getManager();
      $optionalsAttendanceRepo = $doc->getRepository(OptionalsAttendance::class);
      $monthAccountRepo = $doc->getRepository(MonthAccount::class);
      $monthYear = $data['year_month'];

      $startDate = $data['start_date'];
      $endDate = clone $data['end_date']->setTime(23, 59);

      foreach ($selectedStudents as $student) {
        $allOptionals = $optionalsAttendanceRepo->getAllOptionalsByStudent($student, $startDate, $endDate);
      
        $account = $monthAccountRepo->findBy([
            'student' => $student, 
            'accYearMonth' => $monthYear
        ]);

        if ($account) {
          $account = $account[0];  
        } else {
          continue;
        }
        
        foreach ($allOptionals as $optional) {
          if(!$optional) {
            continue;
          }
          
          $optionalAttendance = $optional['oOptionalsAttendance'];

          $count = 1;
          if ($optionalAttendance->getHasAttended()) {
            $count = $optional['optionalCount'];
          }

          //create payment Item for the current Optional
          $payItem = new PaymentItem();
          $payItem->setMonthAccount($account);
          $payItem->setItemName($optionalAttendance->getClassOptional()->getOptionalName());
          $payItem->setItemOptional($optionalAttendance->getClassOptional());
          $payItem->setItemPrice($optionalAttendance->getClassOptional()->getPrice());
          $payItem->setItemCount($count);
          $em->persist($payItem);


          $account->addPaymentItem($payItem);
          $account->addToTotalPrice($payItem->getItemPrice()*$payItem->getItemCount());
          $em->persist($account);
        }
      }

      $em->flush();
      $em->clear(PaymentItem::class);
      $em->clear(MonthAccount::class);
    }


    protected function dataValidation($data, $formView){
      $errors = false;
      if (count($data['pay_item_type']) == 0 ) {
        $this->get('session')->getFlashBag()->add('error', 'ATENȚIE: Vă rugăm să selectați cel puțin o acțiune!');
        $errors = true;
      }

      if (in_array('optionals', $data['pay_item_type'])) {
        
        if (!$data['start_date'] || !$data['end_date'] || $data['start_date'] > $data['end_date']) {
          $this->get('session')->getFlashBag()->add(
            'error', 
            'ATENȚIE: Vă rugăm să introduceți corect datele corespunzătoare pentru intervalul de opționale!'
          );
          $errors = true;
        }
      }
      return $errors;
    }


    /**
     * @Route("/accounts/{monthYear}/{studId}/generate", name="accounts_stud_month_generate")
     * @Method({"GET" , "POST"})
     */
    public function accounts_stud_month_generate($monthYear, $studId)
    {
        $mY = new \DateTime($monthYear);

        $student = $this->getDoctrine()->getRepository
        (Student::class)->find($studId);

        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->findOneBy(
            array('accYearMonth' => $mY, 'student' => $student)
        );

        if (!empty($account)) {
            return $this->redirectToRoute('accounts_stud_month', array(
              'monthYear' => $monthYear,
              'studId' => $studId,
            ) );
        } else {
            $newMonthAccount = new MonthAccount();
            $newMonthAccount->setStudent($student);
            $newMonthAccount->setAccYearMonth($mY);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newMonthAccount);
            $entityManager->flush();

            //Get Service Tax first
            $schoolService = $student->getEnrollment()->getIdService();
            if ($schoolService->getInAdvance() == true) {

                $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
                $formatter->setPattern('MMMM');
                $mY->modify('+1 month'); //undoing this at the end

                $serviceTaxItem = new PaymentItem();
                $serviceTaxItem->setMonthAccount($newMonthAccount);
                //NOTE: The following should be used instead to show inAdvance status on invoice!!!!
                //$serviceTaxItem->setItemName($schoolService->getServicename().' (avans '.$formatter->format($mY).')');
                $serviceTaxItem->setItemName($schoolService->getServicename().' ('.$formatter->format($mY).')');
                //$serviceTaxItem->setItemName($schoolService->getServicename());
                $serviceTaxItem->setItemPrice($schoolService->getServiceprice());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($serviceTaxItem);
                $entityManager->flush();

                $newMonthAccount->addPaymentItem($serviceTaxItem);
                $newMonthAccount->addToTotalPrice($serviceTaxItem->getItemPrice());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newMonthAccount);
                $entityManager->flush();

                $mY->modify('-1 month'); //undoing previous modification
            } else {
                //TODO: Test Logic for this and/or ammend logic if payment is NOT to be made in advance
                if ( $student->getEnrollment()->getEnrollDate()->modify('+1 month') < $mY->modify('last day of this month')) {
                    $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
                    $formatter->setPattern('MMMM');

                    $serviceTaxItem = new PaymentItem();
                    $serviceTaxItem->setMonthAccount($newMonthAccount);
                    //$serviceTaxItem->setItemName($schoolService->getServicename());
                    $serviceTaxItem->setItemName($schoolService->getServicename().' ('.$formatter->format($mY).')');
                    $serviceTaxItem->setItemPrice($schoolService->getServiceprice());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($serviceTaxItem);
                    $entityManager->flush();

                    $newMonthAccount->addPaymentItem($serviceTaxItem);
                    $newMonthAccount->addToTotalPrice($serviceTaxItem->getItemPrice());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($newMonthAccount);
                    $entityManager->flush();
                } else {
                    //don't add...
                }
            }

            //Get Optional Taxes
            $attendances = $this->getDoctrine()->getRepository
            (OptionalsAttendance::class)->findAllForStudByMonth($mY, $student);

            $paymentOptionals = array();
            $paymentOptionalsCount = array();

            foreach ($attendances as $attendance) {
              if (!in_array($attendance->getClassOptional(), $paymentOptionals)) {
                if ($attendance->getClassOptional()->getUseAttend() == true) {
                  if ($attendance->getHasAttended() == true){
                    $paymentOptionals[]=$attendance->getClassOptional();
                    $paymentOptionalsCount[$attendance->getClassOptional()->getOptionalName()] = 1;
                  }
                } else {
                  $paymentOptionals[]=$attendance->getClassOptional();
                  $paymentOptionalsCount[$attendance->getClassOptional()->getOptionalName()] = 1;
                }
              } else {
                if ($attendance->getClassOptional()->getUseAttend() == true) {
                  if ($attendance->getHasAttended() == true){
                    $paymentOptionalsCount[$attendance->getClassOptional()->getOptionalName()]++;
                  }
                }
              }
            }

            foreach ($paymentOptionals as $paymentOptional) {
                $payItem = new PaymentItem();
                $payItem->setMonthAccount($newMonthAccount);
                $payItem->setItemName($paymentOptional->getOptionalName());
                $payItem->setItemPrice($paymentOptional->getPrice());
                $payItem->setItemCount($paymentOptionalsCount[$paymentOptional->getOptionalName()]);
                $payItem->setItemOptional($paymentOptional);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($payItem);
                $entityManager->flush();

                $newMonthAccount->addPaymentItem($payItem);
                $newMonthAccount->addToTotalPrice($payItem->getItemPrice()*$payItem->getItemCount());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newMonthAccount);
                $entityManager->flush();
            }

            //TODO: Get Transport Taxes

            return $this->redirectToRoute('accounts_stud_month', array(
              'monthYear' => $monthYear,
              'studId' => $studId,
            ) );
        }

    }

    /**
     * @Route("/accounts/{monthYear}/{studId}", name="accounts_stud_month")
     */
    public function accounts_stud_month($monthYear, $studId)
    {
        //CAREFUL with $my - it is used for the search then modified to last day of that month
        $mY = new \DateTime($monthYear);

        $student = $this->getDoctrine()->getRepository
        (Student::class)->find($studId);

        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->findOneBy(
            array('accYearMonth' => $mY, 'student' => $student)
        );

        // Debug query for repository
        // $attendances = $this->getDoctrine()->getRepository
        // (OptionalsAttendance::class)->findAllForStudByMonth($mY, $student);

        $mY->modify('last day of this month');

        return $this->render('accounts/accounts.stud.month.html.twig', [
            'student' => $student,
            'month_account' => $account,
            'month_year' => $monthYear,
            'last_day_of_month' => $mY,
            // 'temp_attend_debug' => $attendances,
        ]);
    }




}
