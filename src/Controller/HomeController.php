<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\User;
use App\Entity\ClassOptional;
use App\Entity\MonthAccount;
use App\Entity\OptionalsAttendance;
use App\Entity\AccountInvoice;
use App\Entity\AccountReceipt;

#can use entity's form
use App\Form\UserMyaccountType;
use App\Form\UserMyaccountEnrollType;
use App\Form\UserMyaccountInvoiceType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends Controller
{

    # the following is created to encode the password
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
      $this->encoder = $encoder;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        if ($this->getUser()->getUsertype() === 'ROLE_PARENT') {
          return $this->redirectToRoute("myaccount");
        } else {
        return $this->render('home/index.html.twig', [
            //'controller_name' => 'HomeController',
          ]);
        }
    }

    /**
     * @Route("/account", name="myaccount")
     */
    public function index_parent()
    {
        if ($this->getUser()->getUsertype() === 'ROLE_ADMIN') {
          return $this->redirectToRoute("index");
        } else {
          $kids = $this->getUser()->getGuardianacc()->getChildren();
          $accounts = array();
          foreach ($kids as $kid) {
            $theStudent = $kid->getChildLatestEnroll()->getStudent();
            if ($theStudent != null) { array_push($accounts, $theStudent->getLatestMonthAccount()); }
          }
          if (count($accounts) > 0) {
            $invoices = array();
            foreach ($accounts as $account) {
              if ($account != null) {
                $accInvoices=$account->getAccountInvoices();
                foreach ($accInvoices as $invoice) {
                  if ($invoice != null) {
                    array_push($invoices, $invoice);
                  }
                }
              }
            }
            if (count($invoices) > 0) {
              $latestInvoice = false;
              foreach ($invoices as $invoice) {
                if ($invoice != null && ($latestInvoice == false || $latestInvoice->getId() < $invoice->getId())) {
                  $latestInvoice = $invoice;
                }
              }
            } else {
              $latestInvoice = false;
            }
          } else {
            $latestInvoice = false;
          }
          return $this->render('home/index.parent.html.twig', [
              'latest_invoice' => $latestInvoice,
          ]);
        }
    }

    /**
     * @Route("/account/settings", name="myaccount_settings")
     */
    public function myaccount_settings(Request $request)
    {
        if ($this->getUser()->getUsertype() === 'ROLE_ADMIN') {
          return $this->redirectToRoute("index");
        } else {
          $user = new User();
          $user = $this->getUser();

          $originalPassword = $user->getPassword();

          $form = $this->CreateForm(UserMyaccountType::Class, $user);

          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()) {

            if(!empty($user->getPassword())){
              $user->setPassword(
                $this->encoder->encodePassword($user, $user->getPassword())
              );
            } else {
              $user->setPassword($originalPassword);
            }
            //NOTE: no need to persist when editing
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Informațiile dumneavoastră au fost salvate cu succes!'
            );

            return $this->redirectToRoute('myaccount_settings');
          }

          return $this->render('home/myaccount.settings.html.twig', [
              'form' => $form->createView(),
          ]);
        }
    }

    /**
     * @Route("/account/optionals", name="myaccount_optionals")
     */
    public function myaccount_optionals(Request $request)
    {
        if ($this->getUser()->getUsertype() === 'ROLE_ADMIN') {
          return $this->redirectToRoute("index");
        } else {

          $kids = $this->getUser()->getGuardianacc()->getChildren();

          $views = array();

          foreach ($kids as $kid) {
            $student = $kid->getChildLatestEnroll()->getStudent();
              if (!empty($student)) {
              $optionals = $student->getSchoolUnit()->getClassOptionals();

              $form = $this->createForm(UserMyaccountEnrollType::Class, $student, array(
                'optionals' => $optionals,
              ));
              $forms[] = $form;
              $views[] = $form->createView();
            }
          }

          if ($request->isMethod('POST')) {

              foreach ($forms as $form) {
                $form->handleRequest($request);
              }

              foreach ($forms as $form) {
                if ($form->isSubmitted()) {
                  $student = $form->getData();

                  foreach ($optionals as $optional) {
                    if ($student->getClassOptionals()->contains($optional)) {
                      $optional->addStudent($student);
                    } else {
                      $optional->removeStudent($student);
                    }
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();

                    if (!$optional->isSyncd()) {
                      if ($optional->isModified()) {
                        $this->home_update_optional_attendance($optional);
                      } else {
                        $canCreate = false;
                        if ($optional->getStudents()->count() > 0) {
                          foreach($optional->getOptionalSchedules() as $schedule) {
                            if ($schedule->getScheduledDateTime() > new \DateTime('now')) {
                              $canCreate = true;
                            }
                          }
                        }
                        if ($canCreate == true) {
                          $this->home_generate_optional_attendance($optional);
                        } else {
                          //return $this->redirectToRoute('myaccount_optionals');
                        }
                      }
                    } else {
                      //return $this->redirectToRoute('myaccount_optionals');
                    }
                  }

                  $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Informația a fost salvată cu succes!'
                  );


                  return $this->redirectToRoute('myaccount_optionals');

                }
              }

          }

        return $this->render('home/myaccount.optionals.html.twig', [
            'forms' => $views,
          ]);
        }
    }

    /**
     * @Route("/account/invoices", name="myaccount_invoices")
     */
    public function myaccount_invoices(Request $request, \Swift_Mailer $mailer)
    {
        if ($this->getUser()->getUsertype() === 'ROLE_ADMIN') {
          return $this->redirectToRoute("index");
        } else {

          $kids = $this->getUser()->getGuardianacc()->getChildren();

          $views = array();
          $allAccounts = array();
          $pricePaid = array();

          foreach ($kids as $kid) {
            $student = $kid->getChildLatestEnroll()->getStudent();
              if (!empty($student)) {

                $accounts = $this->getDoctrine()->getRepository
                (MonthAccount::class)->findBy(['student' => $student], ['accYearMonth' => 'DESC']);

                $allAccounts[$student->getUser()->getUsername()] = $accounts;

                foreach ($accounts as $account) {
                  foreach ($account->getAccountInvoices() as $invoice) {

                    $form = $this->createForm(UserMyaccountInvoiceType::Class, $invoice, array(
                      //'optionals' => $optionals,
                    ));
                    $pricePaid[$invoice->getId()] = $invoice->getInvoicePaid();
                    $forms[] = $form;
                    $views[] = $form->createView();
                  }
                }
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

                    $oldPaidPrice = $pricePaid[$invoice->getId()];
                    $account = $invoice->getMonthAccount();
                    $account->setTotalPaid($account->getTotalPaid() - $oldPaidPrice + $invoice->getInvoicePaid());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($account);
                    $entityManager->flush();

                    //TODO send email to admin@iteachsmart.ro

                    $message = (new \Swift_Message('NOTIFICARE Plată nouă - '.$invoice->getMonthAccount()->getStudent()->getUser()->getRoName()))
                      ->setFrom('no-reply@iteachsmart.ro')
                      ->setTo('admin@iteachsmart.ro')
                      //->setTo('dj.diablo.x+tt@gmail.com')
                      ->setBody(
                          $this->renderView(
                              // templates/emails/registration.html.twig
                              'home/email.new.payment.html.twig',
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
                    //$response = new Response();
                    //$response->send();

                    return $this->redirectToRoute('myaccount_invoices');
                  } else {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        //(string) $form->getErrors(true, false)
                        $form->getErrors(true)
                    );
                  }
                }
              }
          }

          return $this->render('home/myaccount.invoices.html.twig', [
              'all_accounts' => $allAccounts,
              'forms' => $views,
            ]);

        }
    }

    /**
     * @Route("/account/invoice_pdf/xR95z9rk{invId}%wrHHz00xE4agWWefErt", name="myacc_invoice_pdf")
     * @Method({"GET"})
     */
    public function myacc_invoice_pdf(Request $request, $invId)
    {
      //$invId = $request->attributes->get('invId');

      $invoice = $this->getDoctrine()->getRepository
      (AccountInvoice::class)->find($invId);

      $snappy = $this->get('knp_snappy.pdf');
      $html = $this->renderView('accounts/invoice_pdf.html.twig',
        array(
            'invoice'  => $invoice
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
      //console.log('A mers!');
      // $response = new Response();
      // $response->send();
    }

    /**
     * @Route("/account/invoice_proof/0xE4agwrHe0WzefErt{invId}R95zx9rk/{action}", name="myacc_invoice_proof")
     * @Method({"GET", "POST"})
     */
    public function myacc_invoice_proof($invId, $action)
    {
        $invoice = $this->getDoctrine()->getRepository
        (AccountInvoice::class)->find($invId);

        $fileName = $invoice->getPayProof();
        if ($fileName != null) {
          $filePath = $this->getParameter('invoice_directory').'/'.$fileName;

          if ($action=='download') {
            return $this->file($filePath);
          } else if ($action=='view') {
            return $this->file($filePath, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
          } else {

          }
        } else {
          $this->get('session')->getFlashBag()->add(
              'notice',
              //(string) $form->getErrors(true, false)
              'Nu există o dovadă de plată atașată acestei facturi!'
          );
          return $this->redirectToRoute('myaccount_invoices');
        }
    }

    /**
     * @Route("/account/receipt_pdf/0xE4agwrHe0Ert{recId}Wzefx9rkR95z", name="myacc_receipt_pdf")
     * @Method({"GET"})
     */
    public function myacc_receipt_pdf(Request $request, $recId)
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

    public function home_update_optional_attendance($currentOptional) { // MUST MATCH CODE FROM ATTENDANCE CONTROLLER!!!!
      //the following checks for new schedules or students and adds them accordingly
      foreach ($currentOptional->getOptionalSchedules() as $sched) {
          foreach ($currentOptional->getStudents() as $stud) {
              $result = $this->getDoctrine()->getRepository(OptionalsAttendance::class)->findOneBy(
                  array('optionalSchedule' => $sched, 'student' => $stud)
              );
              if (!$result && ($sched->getScheduledDateTime() > (new \DateTime('now'))) ) {
                  $attendanceRecord = new OptionalsAttendance();
                  $attendanceRecord->setClassOptional($currentOptional);
                  $attendanceRecord->setOptionalSchedule($sched);
                  $attendanceRecord->setStudent($stud);
                  $attendanceRecord->setHasAttended(0);

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($attendanceRecord);
                  $entityManager->flush();
              } else {
                  //do nothing
              }
          }
      }

      //the following checks for removed students and removes attendance entries accordingly
      $currentAttendances = $currentOptional->getOptionalsAttendances();
      //orphan removal should work for schedules, otherwise create logic here
      foreach ($currentAttendances as $attendance) {
          $student = $attendance->getStudent();

          if (!$currentOptional->getStudents()->contains($student)) {
              if ($attendance->getOptionalSchedule()->getScheduledDateTime() > (new \DateTime('now'))) {
                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->remove($attendance);
                  $entityManager->flush();
              }
          } else {
              //do nothing
          }
      }
    }

    public function home_generate_optional_attendance($currentOptional) { // MUST MATCH CODE FROM ATTENDANCE CONTROLLER!!!!
      if (count($currentOptional->getOptionalsAttendances()) == 0) {
        foreach ($currentOptional->getOptionalSchedules() as $sched) {
          if ($sched->getScheduledDateTime() > (new \DateTime('now'))) {
            foreach ($currentOptional->getStudents() as $stud) {
                $attendanceRecord = new OptionalsAttendance();
                $attendanceRecord->setClassOptional($currentOptional);
                $attendanceRecord->setOptionalSchedule($sched);
                $attendanceRecord->setStudent($stud);
                $attendanceRecord->setHasAttended(0);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($attendanceRecord);
                $entityManager->flush();
            }
          }
        }
      }
    }
}
