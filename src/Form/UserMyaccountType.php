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

class UserMyaccountType extends AbstractType
{

    // /**
    //  * {@inheritdoc}
    //  */
    // public static function getSubscribedEvents()
    // {
    //     return [FormEvents::PRE_SUBMIT => 'preSubmit'];
    // }
    //
    // public function preSubmit(FormEvent $event)
    // {
    //     if ($event->getForm()->getConfig()->getType()->getName() !== 'repeated') {
    //         throw new \UnexpectedValueException(sprintf(
    //             'Expected FormType of type "repeated", "%s" given',
    //             $event->getForm()->getConfig()->getType()->getName()
    //         ));
    //     }
    //
    //     if (!is_array($event->getData())) {
    //         $event->setData(['first' => $event->getData(), 'second' => null]);
    //     }
    // }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'invalid_message' => 'Cele două câmpuri trebuie să coincidă!',
                'options' => array('attr' => array('class' => 'form-control')),
                'required' => true,
                'empty_data' => '',
                'first_options'  => array('label' => 'Parolă'),
                'second_options' => array('label' => 'Repetă Parola'),
                'error_mapping' => array(
                    '.' => 'first',
                ),
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,

        ]);
    }
}
