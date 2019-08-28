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

use App\Entity\ClassOptional;

class SYStep4Type extends AbstractType
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
            ->add('optionalName', TextType::class, array(
              'label' => 'Nume opțional',
              'attr' => array('class' => 'form-control')
            ))
            ->add('description', TextareaType::class, array(
              'label' => 'Descriere',
              'attr' => array('class' => 'form-control'),
            ))
            ->add('useAttend', CheckboxType::class, array(
              'label'    => 'Taxare pe prezență?',
              'required' => false,
              'attr' => array('class' => 'form-check form-check-inline'),
            ))
            ->add('price', MoneyType::class, array(
              'label' => 'Preț',
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array('class' => 'form-control'),
            ))
            //TODO add Included in Services - not immediately required by project
            //->add('inServices')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClassOptional::class,
        ]);
    }
}
