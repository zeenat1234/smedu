<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\User;
use App\Entity\SchoolYear;
use App\Entity\Enrollment;
use App\Entity\ClassOptional;
use App\Entity\MonthAccount;
use App\Entity\OptionalsAttendance;
use App\Entity\OptionalEnrollRequest;
use App\Entity\AccountInvoice;
use App\Entity\AccountReceipt;
use App\Entity\PaymentProof;

#can use entity's form
use App\Form\UserMyaccountType;
use App\Form\UserMyaccountEnrollType;
use App\Form\UserMyaccountSmartProofType;
//use App\Form\UserMyaccountInvoiceType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

            // DO WORK

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
                if (($invoice != null) && ($invoice->getIsLocked() == true) && ($latestInvoice == false || $latestInvoice->getId() < $invoice->getId())) {
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

            if (empty($user->getSecondaryEmail()) && $user->getNotifySecond() == true) {
              $this->get('session')->getFlashBag()->add(
                'error',
                'Ai ales să trimiți notificări celui de-al doilea e-mail, dar nu ai specificat unul! Opțiunea nu a fost salvată.'
              );
              $user->setNotifySecond(false);
            }

            if ($user->getCustomInvoicing() == true) {
              if ($user->getIsCompany() == false) {
                if (empty($user->getInvoicingName()) ||
                empty($user->getInvoicingAddress()) ||
                empty($user->getInvoicingIdent()) )
                {
                  $user->setCustomInvoicing(false);

                  $user->setInvoicingName(null);
                  $user->setInvoicingAddress(null);
                  $user->setInvoicingIdent(null);
                  $user->setInvoicingCompanyReg(null);
                  $user->setInvoicingCompanyFiscal(null);

                  $this->get('session')->getFlashBag()->add(
                    'error',
                    'Detaliile de facturare nu au fost salvate. Pentru Persoană fizică, te rugăm să introduci Nume, Adresă și CNP!'
                  );
                }
              } else {
                if (empty($user->getInvoicingName()) ||
                empty($user->getInvoicingAddress()) )
                {
                  $user->setCustomInvoicing(false);

                  $user->setIsCompany(false);

                  $user->setInvoicingName(null);
                  $user->setInvoicingAddress(null);
                  $user->setInvoicingIdent(null);
                  $user->setInvoicingCompanyReg(null);
                  $user->setInvoicingCompanyFiscal(null);

                  $this->get('session')->getFlashBag()->add(
                    'error',
                    'Detaliile de facturare nu au fost salvate. Pentru Firmă, te rugăm să specifici cel puțin Numele firmei și Adresa!'
                  );
                }
              }
            } else {
              $user->setCustomInvoicing(false);

              $user->setIsCompany(false);

              $user->setInvoicingName(null);
              $user->setInvoicingAddress(null);
              $user->setInvoicingIdent(null);
              $user->setInvoicingCompanyReg(null);
              $user->setInvoicingCompanyFiscal(null);
            }

            //NOTE: no need to persist when editing
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $oldToken = $this->container->get('security.token_storage')->getToken();

            // create the authentication token
            $token = new UsernamePasswordToken(
                $user, //user object with updated username
                $user->getPassword(),
                $oldToken->getProviderKey(),
                $oldToken->getRoles());
            // update the token in the security context
            $this->container->get('security.token_storage')->setToken($token);

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
                  $optionals = $student->getSchoolUnit()->getClassOptionals();

                  $addRequest = new OptionalEnrollRequest();
                  $addRequest->setRUser($this->getUser());
                  $addRequest->setRDateTime(new \DateTime('now'));
                  $addRequest->setRStudent($student);
                  $addRequest->setIsPending(1);
                  $addRequest->setRType(1);

                  $removeRequest = new OptionalEnrollRequest();
                  $removeRequest->setRUser($this->getUser());
                  $removeRequest->setRDateTime(new \DateTime('now'));
                  $removeRequest->setRStudent($student);
                  $removeRequest->setIsPending(1);
                  $removeRequest->setRType(0);

                  foreach ($optionals as $optional) {
                    if (!$optional->getStudents()->contains($student) && $student->getClassOptionals()->contains($optional) ) {
                      //create add request
                      $editable = true;
                      foreach ($student->getOptionalEnrollRequests() as $enrollRequest) {
                        if ($enrollRequest->getROptionals()->contains($optional) && $enrollRequest->getIsPending() == true) {
                          $editable = false;
                        }
                      }
                      if ($editable == true) {
                        $addRequest->addROptional($optional);
                      }
                    }

                    elseif ($optional->getStudents()->contains($student) && !$student->getClassOptionals()->contains($optional)) {
                      //create remove request
                      $editable = true;
                      foreach ($student->getOptionalEnrollRequests() as $enrollRequest) {
                        if ($enrollRequest->getROptionals()->contains($optional) && $enrollRequest->getIsPending() == true) {
                          $editable = false;
                        }
                      }
                      if ($editable == true) {
                        $removeRequest->addROptional($optional);
                      }
                    }
                  }

                  if ($addRequest->getROptionals()->count() > 0) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($addRequest);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->add(
                      'notice',
                      'Cererea ta pentru înscriere a fost înregistrată cu succes!'
                    );
                  }

                  if ($removeRequest->getROptionals()->count() > 0) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($removeRequest);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->add(
                      'notice',
                      'Cererea ta pentru anularea înscrierii a fost înregistrată cu succes!'
                    );
                  }

                  // foreach ($optionals as $optional) {
                  //   if ($student->getClassOptionals()->contains($optional)) {
                  //     $optional->addStudent($student);
                  //   } else {
                  //     $optional->removeStudent($student);
                  //   }
                  //   $entityManager = $this->getDoctrine()->getManager();
                  //   $entityManager->flush();
                  //
                  //   if (!$optional->isSyncd()) {
                  //     if ($optional->isModified()) {
                  //       $this->home_update_optional_attendance($optional);
                  //     } else {
                  //       $canCreate = false;
                  //       if ($optional->getStudents()->count() > 0) {
                  //         foreach($optional->getOptionalSchedules() as $schedule) {
                  //           if ($schedule->getScheduledDateTime() > new \DateTime('now')) {
                  //             $canCreate = true;
                  //           }
                  //         }
                  //       }
                  //       if ($canCreate == true) {
                  //         $this->home_generate_optional_attendance($optional);
                  //       } else {
                  //         //return $this->redirectToRoute('myaccount_optionals');
                  //       }
                  //     }
                  //   } else {
                  //     //return $this->redirectToRoute('myaccount_optionals');
                  //   }
                  // }

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
     * @Route("/account/optionals/revoke/{id}", name="myaccount_optionals_revoke")
     * @Method({"GET", "POST"})
     */
    public function myaccount_optionals_revoke($id)
    {
      $theRequest = $this->getDoctrine()->getRepository
      (OptionalEnrollRequest::class)->find($id);

      $theRequest->setIsPending(0);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($theRequest);
      $entityManager->flush();

      $this->get('session')->getFlashBag()->add(
        'notice',
        'Cererea a fost ANULATĂ!'
      );

      return $this->redirectToRoute('myaccount_optionals');
    }

    /**
     * @Route("/account/opt_attendance", name="opt_attendance")
     * @Method({"GET", "POST"})
     */
    public function opt_attendance()
    {
      return $this->render('home/myaccount.attendance.opt.html.twig', [
        //'vars' => $vars,
      ]);
    }

    /**
     * @Route("/account/transp_attendance", name="transp_attendance")
     * @Method({"GET", "POST"})
     */
    public function transp_attendance()
    {
      return $this->render('home/myaccount.attendance.transp.html.twig', [
        //'vars' => $vars,
      ]);
    }

    /**
     * @Route("/account/invoices/{yearId?0}", name="myaccount_invoices")
     */
    public function myaccount_invoices(Request $request, \Swift_Mailer $mailer, $yearId)
    {
        if ($this->getUser()->getUsertype() === 'ROLE_ADMIN') {
          return $this->redirectToRoute("index");
        } else {

          $kids = $this->getUser()->getGuardianacc()->getChildren();

          $views = array();
          $allAccounts = array();
          $pricePaid = array();

          $schoolYear = null;
          if ($yearId == 0) {
            $schoolYear = $this->getDoctrine()->getRepository
            (SchoolYear::class)->findCurrentYear();
          } else {
            $schoolYear = $this->getDoctrine()->getRepository
            (SchoolYear::class)->find($yearId);
          }

          foreach ($kids as $kid) {
            // check if student is actually enrolled in that year
            // useful for previous years where only 1 student might be enrolled out of many
            // useful for new years where not all students were reenrolled
            $hasEnroll = false;
            $enrollment = null;
            if ($yearId == 0) {
              $enrollment = $kid->getChildLatestEnroll();
              $hasEnroll = true;
            } else {
              $enrollment = $this->getDoctrine()->getRepository
              (Enrollment::class)->findOneBy(array(
                'idChild' => $kid->getId(),
                'schoolYear' => $yearId,
              ));
              if ($enrollment != NULL) {
                $hasEnroll = true;
              }
            }

            if ($hasEnroll) {
              $student = $enrollment->getStudent();

              if (!empty($student)) {

                $accounts = $this->getDoctrine()->getRepository
                (MonthAccount::class)->findBy(['student' => $student], ['accYearMonth' => 'DESC']);

                $allAccounts[$student->getUser()->getUsername()] = $accounts;

                $paidInvoices = array();
                $unpaidInvoices = array();

                foreach ($accounts as $account) {
                  foreach ($account->getAccountInvoices() as $invoice) {
                    if ($invoice->getIsPaid()) {
                      $paidInvoices[] = $invoice;
                    } else {
                      $unpaidInvoices[] = $invoice;
                    }
                  }
                }

                $allInvoices = array_merge($unpaidInvoices, $paidInvoices);

                foreach ($allInvoices as $invoice) {
                  foreach ($invoice->getPayments() as $payment) {
                    if ($payment->getIsPending()) {
                      $form = $this->createForm(UserMyaccountSmartProofType::Class, $payment);
                      $forms[] = $form;
                      $views[] = $form->createView();
                    }
                  }
                }
              }
            } // end IF HAS ENROLL
          }

          if ($request->isMethod('POST')) {

              foreach ($forms as $form) {
                $form->handleRequest($request);
              }

              foreach ($forms as $form) {
                if ($form->isSubmitted()) {
                  if ($form->isValid()) {

                    $thePayment = $form->getData();
                    $invoice = $thePayment->getPayInvoices()->first();

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

                    return $this->redirectToRoute('myaccount_invoices',array('yearId' => $yearId));
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

          return $this->render('home/myaccount.invoices.new.html.twig', [
              'schoolYear' => $schoolYear,
              'yearId' => $yearId,
              'all_accounts' => $allAccounts,
              'forms' => $views,
            ]);

          // DEPRECATED - old interface
          // return $this->render('home/myaccount.invoices.html.twig', [
          //     'all_accounts' => $allAccounts,
          //     'forms' => $views,
          //   ]);

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


}
