<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\User;
use App\Entity\Student;
use App\Entity\Guardian;
use App\Entity\Enrollment;
use App\Entity\SchoolYear;
use App\Entity\AccountPermission;

#form flow
use Craue\FormFlowBundle\Form\FormFlowInterface;
use App\Form\EnrollWizard\ParentStudentEnroll;

#can use entity's form
use App\Form\UserType;
use App\Form\AccountPermissionType;

#can overwrite form fields from type
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller
{

    # the following is created to encode the password
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
      $this->encoder = $encoder;
    }


    /**
     * @Route("/users/{type?'all'}", name="users")
     * @Method({"GET", "POST"})
     */
    public function users(Request $request, $type)
    {
        if ($type == 'students') {
          $users = $this->getDoctrine()->getRepository
          (User::class)->findBy(['usertype' => 'ROLE_PUPIL'], ['lastName' => 'ASC']);
          $theRole = 'Elevi';
        } elseif ($type == 'parents') {
          $users = $this->getDoctrine()->getRepository
          (User::class)->findBy(['usertype' => 'ROLE_PARENT'], ['lastName' => 'ASC']);
          $theRole = 'Părinți';
        } elseif ($type == 'professors') {
          $users = $this->getDoctrine()->getRepository
          (User::class)->findBy(['usertype' => 'ROLE_PROF'], ['lastName' => 'ASC']);
          $theRole = 'Profesori';
        } elseif ($type == 'admins') {
          $users = $this->getDoctrine()->getRepository
          (User::class)->findBy(['usertype' => 'ROLE_ADMIN'], ['lastName' => 'ASC']);
          $theRole = 'Administratori';
        } elseif ($type == 'managers') {
          $users = $this->getDoctrine()->getRepository
          (User::class)->findBy(['usertype' => 'ROLE_CUSTOM'], ['lastName' => 'ASC']);
          $theRole = 'Manageri';
        } else {
          $users = $this->getDoctrine()->getRepository
          (User::class)->findBy([], ['lastName' => 'ASC']);
          $theRole = 'Toți utilizatorii';
        }

        $views = array(); //required in case there are no views available

        foreach ($users as $user) {
          if ($user->getUsertype() == 'ROLE_CUSTOM') {
            $form = $this->createForm(AccountPermissionType::Class, $user);

            $forms[] = $form;
            $views[] = $form->createView();
          }
        }

        if ($request->isMethod('POST')) {

            foreach ($forms as $form) {
              $form->handleRequest($request);
            }

            $allPermissions = $this->getDoctrine()->getRepository
            (AccountPermission::class)->findAll();

            foreach ($forms as $form) {
              if ($form->isSubmitted()) {
                if ($form->isValid()) {
                  $user = $form->getData();

                  foreach ($allPermissions as $permission) {
                    if ($user->getAccountPermissions()->contains($permission)) {
                      $permission->AddUser($user);
                    } else {
                      $permission->RemoveUser($user);
                    }
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();
                  }

                  return $this->redirectToRoute('users', array('type' => 'managers'));
                }
              }
            }
          }

        return $this->render('user/users.html.twig', array(
          'users' => $users,
          'role' => $theRole,
          'forms' => $views,
        ));
    }

    /**
     * @Route("/user/new", name="user_new")
     * @Method({"GET", "POST"})
     */
    public function new(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::Class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

          $user = $form->getData();

          $user->setPassword(
            $this->encoder->encodePassword($user, $user->getPassword())
          );

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

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($user);
          $entityManager->flush();

          return $this->redirectToRoute('users');
        }

        return $this->render('user/user.new.html.twig', array(
          'form' => $form->createView()
        ));
    }

    /**
     * @Route("/user/edit/{id}.{studId?0}.{redirect?'users'}", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, $id, $redirect, $studId)
    {
        $user = new User();

        $user = $this->getDoctrine()->getRepository
        (User::class)->find($id);

        $originalPassword = $user->getPassword();

        $form = $this->CreateForm(UserType::Class, $user);

        //TODO find a way to maybe have this logic inside UserType.php
        $form->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Cele două câmpuri trebuie să coincidă!',
            'options' => array('attr' => array('class' => 'form-control')),
            'required' => false,
            'empty_data' => '',
            'first_options'  => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password')
          ))
            ->add('usertype', ChoiceType::class, array(
              'label' => 'Tip utilizator:',
              'choices'  => array(
                'Profesor' => 'ROLE_PROF',
                'Administrator' => 'ROLE_ADMIN',
                'Manager' => 'ROLE_CUSTOM',
                //The following 2x roles can only be used when editing entries
                'Părinte' => 'ROLE_PARENT',
                'Elev' => 'ROLE_PUPIL'
              ),
              'disabled' => true,
              'attr' => array('class' => 'form-control')
            ));


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

          if (!empty($user->getPassword())) {
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
          $entityManager->flush();

          if ($redirect == 'users') { //DEBUG: the first condition is not met - investigate
            return $this->redirectToRoute('users');
          } else if ($redirect == 'class_group') {
            $student = $this->getDoctrine()->getRepository
            (Student::class)->find($studId);
            return $this->redirectToRoute('class_group_view', ['groupId' => $student->getClassGroup()->getId()]);
          } else {
            return $this->redirectToRoute('users');
          }
        }

        return $this->render('user/user.edit.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    //TODO Find out how to fix 500 error which occurs in the browser console
    //when a delete statement is executed
    /**
     * @Route("/user/delete/{id}", name="user_delete")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id)
    {
      $user = $this->getDoctrine()->getRepository
      (User::class)->find($id);

      if ($user->getUsertype() == 'ROLE_PARENT') {

        $guardAcc = $user->getGuardianacc();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($guardAcc);
        $entityManager->flush();
      }

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($user);
      $entityManager->flush();

      //console.log('A mers!');
      $response = new Response();
      $response->send();

      //return $this->redirectToRoute('users');
    }

    /**
     * @Route("/user/reset/{id}", name="user_reset")
     * @Method({"GET", "POST"})
     */
    public function reset_mail(Request $request, $id, \Swift_Mailer $mailer) //LOGIC SIMILAR FOR ANNONYMOUS USER RESET PASS
    {
      $user = $this->getDoctrine()->getRepository
      (User::class)->find($id);

      $plainpass = $this->randomPassword();

      $user->setPassword(
        $this->encoder->encodePassword($user, $plainpass)
      );

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();

      $secondaryEmail='';
      if ($user->getNotifySecond()) {
        $secondaryEmail = $user->getSecondaryEmail();
      }

      $message = (new \Swift_Message('E-mail Resetare Parolă - Planeta Copiilor'))
        ->setFrom('no-reply@iteachsmart.ro')
        ->setTo($user->getEmail())
        ->setCc($secondaryEmail)
        ->setBody(
            $this->renderView(
                // templates/emails/registration.html.twig
                'user/email.user.reset.html.twig',
                array('user' => $user, 'plainpass' => $plainpass)
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
     * @Route("/user/{id}", name="user_show")
     */
    public function show($id)
    {
        $user = $this->getDoctrine()->getRepository
        (User::class)->find($id);

        return $this->render('user/user.show.html.twig', array(
          'user' => $user
        ));
    }


    /**
     * @Route("/enrollwizard", name="enroll_wizard")
     * @Method({"GET", "POST"})
     */
    public function enrollWizard()
    {
        return $this->processFlow(new ParentStudentEnroll(), $this->get('smedu.form.flow.parentStudentEnroll'));
    }

    protected function processFlow($formData, FormFlowInterface $flow) {

        $schoolYears = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentAndNew();

        $flow->setGenericFormOptions(array(
          'school_years' => $schoolYears,
        ));
        //always bind after settingGenericFormOptions
        $flow->bind($formData);

    		$form = $submittedForm = $flow->createForm(ParentStudentEnroll::class, $formData, array(
            // 'school_years' => $schoolYears,
            // 'guardian' => $formData->guardian,
        ));
    		if ($flow->isValid($submittedForm)) {
      			$flow->saveCurrentStepData($submittedForm);
      			if ($flow->nextStep()) {

              $form = $flow->createForm(ParentStudentEnroll::class, $formData, array(
                  'school_years' => $schoolYears,
                  // 'guardian' => $formData->guardian,
              ));

      			} else {
      				// flow finished

              if ($formData->addGuardian) {
                $guardian = $formData->newGuardian;
                $guardian->setPassword(
                  $this->encoder->encodePassword($guardian, $guardian->getPassword())
                );
                $guardian->setUsername($guardian->getFirstName().'.'.$guardian->getLastName());
                $guardian->setUsertype('ROLE_PARENT');

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($guardian);
                $entityManager->flush();

                $guardianAcc = new Guardian();
                $guardianAcc->setUser($guardian);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($guardianAcc);
                $entityManager->flush();
              } else {
                $guardian = $formData->guardian;
                $guardianAcc = $guardian->getGuardianacc();
              }

              if ($formData->addStudent) {
                $student = $formData->newStudent;
                $student->setPassword(
                  $this->encoder->encodePassword($student, $student->getPassword())
                );
                $student->setUsername($student->getFirstName().'.'.$student->getLastName());
                $student->setUsertype('ROLE_PUPIL');
                $student->setEmail($student->getFirstName().'.'.$student->getLastName().'@iteachsmart.ro');
                $student->setPhoneNo('0');
                $student->setGuardian($guardianAcc);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($student);
                $entityManager->flush();
              } else {
                $student = $formData->student;
              }

              $enrollment = $formData->enrollment;

              //TODO: the following 2x lines may be redundant - check without this logic as well
              $enrollment->setIdParent($guardian);
              $enrollment->setIdChild($student);

              //Same code is used in EnrollmentController
              $newStudent = new Student();
              $newStudent->setUser($student);
              $newStudent->setSchoolUnit($formData->schoolUnit);
              $newStudent->setEnrollment($enrollment);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($newStudent);
              $entityManager->flush();

              $enrollment->setStudent($newStudent);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($enrollment);
              $entityManager->flush();

              //TODO: Send e-mail after creating enrollment

              //flow reset and redirect
      				$flow->reset();
      				return $this->redirect($this->generateUrl('all_enrollments'));
      			}
    		}
        return $this->render('user/parent.student.enroll.html.twig', array(
        		'form' => $form->createView(),
        		'flow' => $flow,
            'formData' => $formData,
      	));
  	}

    private function randomPassword() {

      $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
      $pass = array(); //remember to declare $pass as an array
      $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

      for ($i = 0; $i < 8; $i++) {
          $n = rand(0, $alphaLength);
          $pass[] = $alphabet[$n];
      }

      return implode($pass); //turn the array into a string
    }

}
