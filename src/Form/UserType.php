<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

# Symfony4 best practice is to not use a submit type in the formType or Controller
#use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
              'attr' => array('class' => 'form-control')
            ))
            ->add('lastName', TextType::class, array(
              'label' => 'Nume',
              'attr' => array('class' => 'form-control')
            ))
            ->add('firstName', TextType::class, array(
              'label' => 'Prenume',
              'attr' => array('class' => 'form-control')
            ))
            ->add('email', EmailType::class, array(
              'label' => 'E-Mail',
              'attr' => array('class' => 'form-control')
            ))
            ->add('phoneNo', TextType::class, array(
              'label' => 'Număr de telefon',
              'attr' => array('class' => 'form-control')
            ))
            ->add('password', RepeatedType::class, array(
              'type' => PasswordType::class,
              'invalid_message' => 'The password fields must match.',
              'options' => array('attr' => array('class' => 'form-control')),
              'required' => true,
              'first_options'  => array('label' => 'Password'),
              'second_options' => array('label' => 'Repeat Password')
            ))
            ->add('usertype', ChoiceType::class, array(
              'choices'  => array(
                'Administrator' => 'ROLE_ADMIN',
                'Profesor' => 'ROLE_PROF',
                'Părinte' => 'ROLE_PARENT',
                'Elev' => 'ROLE_PUPIL'
              ),
              'label' => 'Tip Utilizator',
              'attr' => array('class' => 'form-control')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
