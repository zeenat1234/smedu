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
            ))
            ->add('secondaryEmail', EmailType::class, array(
              'label' => 'E-Mail Secundar',
              'required' => false,
              'attr' => array('class' => 'form-control')
            ))
            ->add('notifySecond', ChoiceType::class, array(
              'label' => 'Trimite notificări și către e-mailul secundar',
              'choices' => array(
                'NU Trimite' => false,
                'Trimite' => true,
              ),
              'expanded' => true,
              'multiple' => false,
              'attr' => array('class' => '')
            ))
            ->add('customInvoicing', ChoiceType::class, array(
              'label' => 'Informații pentru facturare',
              'choices' => array(
                'Nefurnizate' => false,
                'Furnizate' => true,
              ),
              'expanded' => true,
              'multiple' => false,
            ))
            ->add('isCompany', ChoiceType::class, array(
              'label' => 'Tip factură',
              'choices' => array(
                'Persoană Fizică' => false,
                'Firmă' => true,
              ),
              'expanded' => true,
              'multiple' => false,
            ))
            ->add('invoicingName', TextType::class, array(
              'label' => 'Nume',
              'required' => false,
              'attr' => array('class' => 'form-control')
            ))
            ->add('invoicingAddress', TextType::class, array(
              'label' => 'Adresă',
              'required' => false,
              'attr' => array('class' => 'form-control')
            ))
            ->add('invoicingIdent', TextType::class, array(
              'label' => 'CNP',
              'required' => false,
              'attr' => array('class' => 'form-control')
            ))
            ->add('invoicingCompanyReg', TextType::class, array(
              'label' => 'Reg. Com.',
              'required' => false,
              'attr' => array('class' => 'form-control')
            ))
            ->add('invoicingCompanyFiscal', TextType::class, array(
              'label' => 'CIF',
              'required' => false,
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
