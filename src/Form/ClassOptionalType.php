<?php

namespace App\Form;

use App\Entity\ClassOptional;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ClassOptionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unitchoice = array();
        $unitchoice[$options['school_unit']->getUnitname()] = $options['school_unit'];

        $profchoice = array();
        foreach ($options['professors'] as $professor) {
            $profchoice[$professor->getFullName(1)] = $professor;
        }

        $builder
            ->add('optionalName', TextType::class, array(
              'label' => 'Nume opțional',
              'attr' => array('class' => 'col-6 form-control')
            ))
            ->add('professor', ChoiceType::class, array(
              'label'  => 'Profesor',
              'choices'  => $profchoice,
              'attr' => array(
                'class' => 'form-control',
              )
            ))
            ->add('description', TextareaType::class, array(
              'label' => 'Descriere',
              'attr' => array('class' => 'col-6 form-control'),
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
              'attr' => array('class' => 'col-3 form-control'),
            ))
            ->add('schoolUnit', ChoiceType::class, array(
              'label' => 'Unitate școlară',
              'choices'  => $unitchoice,
              'attr' => array(
                'class' => 'form-control',
                'readonly' => 'readonly',
              ),
            ))
            //TODO add Included in Services - not immediately required by project
            //->add('inServices')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClassOptional::class,
            'school_unit' => SchoolUnit::class,
            'professors' => array(User::class),
        ]);
    }
}
