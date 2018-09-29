<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

#form type definition
use App\Form\PaymentItemType;
use App\Form\AccountInvoiceType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountsController extends AbstractController
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
     * @Route("/accounts/item/add/{monthAccId}", name="accounts_item_add")
     */
    public function accounts_item_add(Request $request, $monthAccId)
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

          return $this->redirectToRoute('accounts_stud_month', array(
            'monthYear' => $monthYear->format('Y-m'),
            'studId' => $student->getId(),
          ));
        }

        return $this->render('accounts/accounts.item.add.html.twig', [
          'student' => $student,
          'month_year' => $monthYear,
          'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/accounts/item/{itemId}", name="accounts_item_modify")
     */
    public function accounts_item_modify(Request $request, $itemId)
    {

        $payItem = $this->getDoctrine()->getRepository
        (PaymentItem::class)->find($itemId);

        $student = $payItem->getMonthAccount()->getStudent();
        $monthYear = $payItem->getMonthAccount()->getAccYearMonth();

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

          $monthAccount = $payItem->getMonthAccount();

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

          return $this->redirectToRoute('accounts_stud_month', array(
            'monthYear' => $monthYear->format('Y-m'),
            'studId' => $student->getId(),
          ));
        }

        return $this->render('accounts/accounts.item.modify.html.twig', [
          'pay_item' => $payItem,
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
          $invoice->setInvoiceTotal($invoice->getInvoiceTotal() - $removePrice*$removeCount);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($invoice);
          $entityManager->flush();
      }

      //console.log('A mers!');
      $response = new Response();
      $response->send();

    }

    /**
     * @Route("/accounts/payinvoice/{invId}", name="accounts_pay_invoice")
     * @Method({"GET" , "DELETE", "POST"})
     */
    public function accounts_pay_invoice(Request $request, $invId) {

        $invoice = $this->getDoctrine()->getRepository
        (AccountInvoice::class)->find($invId);

        $student = $invoice->getMonthAccount()->getStudent();

        $monthYear = $invoice->getMonthAccount()->getAccYearMonth();

        $form = $this->createForm(AccountInvoiceType::Class, $invoice);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $invoice = $form->getData();

          $invoice->setIsPaid(true);
          $invoice->setPayProof('Temporary Pay Proof');

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($invoice);
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
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($invoice);
            $entityManager->flush();
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        $payItem->setIsInvoiced(false);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($payItem);
        $entityManager->flush();

        return $this->redirectToRoute('account_invoices', array(
          'accId' => $invoice->getMonthAccount()->getId(),
        ) );
    }

    /**
     * @Route("/accounts/{accId}/invoices", name="account_invoices")
     * @Method({"GET"})
     */
    public function account_invoices($accId)
    {
        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($accId);

        $student = $account->getStudent();
        $invoices = $account->getAccountInvoices();

        return $this->render('accounts/account.invoices.html.twig', [
          'student' => $student,
          'account' => $account,
          'month_year' => $account->getAccYearMonth(),
          'payment_items' => $account->getPaymentItems(),
          'invoices' => $invoices,
        ]);
    }

    /**
     * @Route("/accounts/{accId}/invoiceall/{itemCount}", name="account_invoice_all")
     * @Method({"GET","POST"})
     */
    public function account_invoice_all($accId, $itemCount)
    {
        $account = $this->getDoctrine()->getRepository
        (MonthAccount::class)->find($accId);

        $newInvoice = new AccountInvoice();
        $newInvoice->setMonthAccount($account);
        $newInvoice->setInvoiceName('Factură Nr: '.$itemCount);

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
                $newMonthAccount->addToTotalPrice($payItem->getItemPrice());
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
