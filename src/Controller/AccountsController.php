<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

#form type definition
use App\Form\PaymentItemType;
use App\Form\AccountInvoiceType;
use App\Form\AccountInvoiceNumberType;
use App\Form\AccountInvoiceReceiptType;

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
     * @Route("/accounts/invoice_from_proforma/{invId}", name="invoice_from_proforma")
     * @Method({"GET", "POST"})
     */
    public function invoice_from_proforma(Request $request, $invId)
    {
      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

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

      $secondaryEmail='';
      if ($parentUser->getNotifySecond()) {
        $secondaryEmail = $parentUser->getSecondaryEmail();
      }

      $message = (new \Swift_Message($messageTitle.' - Planeta Copiilor'))
        ->setFrom('no-reply@iteachsmart.ro')
        ->setTo($parentUser->getEmail())
        ->setCc($secondaryEmail)
        ->setBody(
            $this->renderView(
                'accounts/invoice_email.html.twig',
                array('invoice' => $invoice)
            ),
            'text/html'
        )
        /*
         * If you also want to include a plaintext version of the message
        ->addPart(
            $this->renderView(
                'emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */
      ;

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

        $views = array(); //required in case there are no views available
        $views2 = array();

        foreach ($invoices as $invoice) {

          $form = $this->createForm(AccountInvoiceNumberType::Class, $invoice);

          $forms[] = $form;
          $views[] = $form->createView();

          //next prepare the receipt forms
          $newReceipt = new AccountReceipt();

          $newReceipt->setAccountInvoice($invoice);
          $newReceipt->setReceiptDate(new \DateTime('now'));

          /* RECEIPT NUMBER LOGIC STARTS HERE */
          $theUnit = $account->getStudent()->getSchoolUnit();
          $iserial = $theUnit->getFirstReceiptSerial();
          $inumber = $theUnit->getFirstReceiptNumber();

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

                  return $this->redirectToRoute('account_invoices', array('accId' => $invoice->getMonthAccount()->getId()));
                } else {
                  $this->get('session')->getFlashBag()->add(
                      'notice',
                      //(string) $form->getErrors(true, false)
                      $form2->getErrors(true)
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
                    $serviceTaxItem = new PaymentItem();
                    $serviceTaxItem->setMonthAccount($newMonthAccount);
                    $serviceTaxItem->setItemName($schoolService->getServicename());
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
            // foreach ($student->getOptionalsAttendancesByMonth($mY) as $attendance) {
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
