<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use App\Entity\SchoolService;

class SYStep2Type extends AbstractType
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
          ->add('serviceName', TextType::class, array(
            'label'    => 'Denumire Serviciu',
            'attr' => array('class' => 'form-control')
          ))
          ->add('serviceDescription', TextareaType::class, array(
            'label'    => 'Descriere',
            'attr' => array('class' => 'form-control'),
          ))
          ->add('inAdvance', CheckboxType::class, array(
            'label'    => 'Taxare în avans?',
            'required' => false,
            'attr' => array('class' => 'form-check form-check-inline'),
          ))
          ->add('servicePrice', MoneyType::class, array(
            'label'    => 'Taxă Lunară',
            'currency' => 'RON',
            'scale' => 2,
            'attr' => array('class' => 'form-control'),
          ))
        ;
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolService::class,
        ]);
    }
}
