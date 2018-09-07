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

#form flow
use Craue\FormFlowBundle\Form\FormFlowInterface;
use App\Form\EnrollWizard\ParentStudentEnroll;

#can use entity's form
use App\Form\UserType;

#can overwrite form fields from type
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

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
     * @Route("/users", name="users")
     * @Method({"GET"})
     */
    public function users()
    {
        $users = $this->getDoctrine()->getRepository
        //(User::class)->findAll();
        (User::class)->findBy([], ['lastName' => 'ASC']);

        return $this->render('user/users.html.twig', array(
          'users' => $users,
          'role' => 'Utilizatori',
        ));
    }

    /**
     * @Route("/users/students", name="users_students")
     * @Method({"GET"})
     */
    public function users_students()
    {
        $users = $this->getDoctrine()->getRepository
        (User::class)->findBy(['usertype' => 'ROLE_PUPIL'], ['lastName' => 'ASC']);

        return $this->render('user/users.html.twig', array(
          'users' => $users,
          'role' => 'Elevi',
        ));
    }

    /**
     * @Route("/users/parents", name="users_parents")
     * @Method({"GET"})
     */
    public function users_parents()
    {
        $users = $this->getDoctrine()->getRepository
        (User::class)->findBy(['usertype' => 'ROLE_PARENT'], ['lastName' => 'ASC']);

        return $this->render('user/users.html.twig', array(
          'users' => $users,
          'role' => 'Părinți',
        ));
    }

    /**
     * @Route("/users/professors", name="users_profs")
     * @Method({"GET"})
     */
    public function users_profs()
    {
        $users = $this->getDoctrine()->getRepository
        (User::class)->findBy(['usertype' => 'ROLE_PROF'], ['lastName' => 'ASC']);

        return $this->render('user/users.html.twig', array(
          'users' => $users,
          'role' => 'Profesori',
        ));
    }

    /**
     * @Route("/users/admins", name="users_admins")
     * @Method({"GET"})
     */
    public function users_admins()
    {
        $users = $this->getDoctrine()->getRepository
        (User::class)->findBy(['usertype' => 'ROLE_ADMIN'], ['lastName' => 'ASC']);

        return $this->render('user/users.html.twig', array(
          'users' => $users,
          'role' => 'Administratori',
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
     * @Route("/user/edit/{id}", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, $id)
    {
        $user = new User();

        $user = $this->getDoctrine()->getRepository
        (User::class)->find($id);

        $originalPassword = $user->getPassword();

        $form = $this->CreateForm(UserType::Class, $user);

        //TODO find a way to maybe have this logic inside UserType.php
        $form->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => array('attr' => array('class' => 'form-control')),
            'required' => false,
            'empty_data' => '',
            'first_options'  => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password')
          ));


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

          if(!empty($user->getPassword())){
            $user->setPassword(
              $this->encoder->encodePassword($user, $user->getPassword())
            );
          } else {
            $user->setPassword($originalPassword);
          }

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();

          return $this->redirectToRoute('users');
        }

        return $this->render('user/user.edit.html.twig', array(
          'form' => $form->createView()
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


      $entityManager = $this->getDoctrine()->getManager();

      $entityManager->remove($user);
      $entityManager->flush();

      //console.log('A mers!');
      $response = new Response();
      $response->send();

      //return $this->redirectToRoute('users');
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
     * @Route("/users/enrollwizard", name="enroll_wizard")
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
}
