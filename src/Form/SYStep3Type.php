<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\ClassGroup;

class SYStep3Type extends AbstractType
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
            ->add('groupName', TextType::class, array(
              'label'  => 'Nume grupă',
              'attr'   => array(
                'class' => 'form-control',
              ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClassGroup::class,
        ]);
    }
}
