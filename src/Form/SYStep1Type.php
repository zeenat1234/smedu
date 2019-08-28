<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\SchoolUnit;

class SYStep1Type extends AbstractType
{

    private $formCount;

    public function __construct()
    {
        $this->formCount = 0;
    }

    public function getBlockPrefix()
    {
        return parent::getBlockPrefix().'_'.$this->formCount;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      ++$this->formCount;

      $builder
        ->add('isImport', ChoiceType::class, array(
          'label' => 'Denumire unitate',
          'expanded' => true,
          'multiple' => false,
          'choices'  => array(
            'Omite' => 0,
            'Import' => 1,
          ),
          'data' => 1,
          'mapped' => false,
        ))
        ->add('unitName', TextType::class, array(
          'label' => 'Denumire unitate',
          'attr' => array('class' => 'form-control')
        ))
        ->add('startDate', DateType::class, array(
          'label' => 'Începutul activității',
          'input' => 'datetime',
          'widget' => 'single_text',
          'attr' => array('type' => 'datetime', 'class' => 'form-control'),
        ))
        ->add('endDate', DateType::class, array(
          'label' => 'Terminarea activității',
          'input' => 'datetime',
          'widget' => 'single_text',
          'attr' => array('type' => 'datetime', 'class' => 'form-control'),
        ))
        ->add('availableSpots', NumberType::class, array(
          'label' => 'Locuri disponibile',
          'attr' => array('class' => 'form-control'),
        ))
        ->add('firstInvoiceSerial', TextType::class, array(
          'label' => 'Serie Facturare',
          'attr' => array('class' => 'form-control'),
        ))
        ->add('firstInvoiceNumber', NumberType::class, array(
          'label' => 'Număr pentru prima Factură',
          'attr' => array('class' => 'form-control'),
        ))
        ->add('firstReceiptSerial', TextType::class, array(
          'label' => 'Serie Chitanțe',
          'attr' => array('class' => 'form-control'),
        ))
        ->add('firstReceiptNumber', NumberType::class, array(
          'label' => 'Număr pentru prima Chitanță',
          'attr' => array('class' => 'form-control'),
        ))
        ->add('description', TextareaType::class, array(
          'label' => 'Descriere',
          'attr' => array('class' => 'form-control'),
        ))
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => SchoolUnit::class,
        ]);
    }
}
