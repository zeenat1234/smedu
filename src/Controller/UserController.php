<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\User;

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
        (User::class)->findAll();

        return $this->render('user/users.html.twig', array(
          'users' => $users
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


}
