<?php

namespace App\Form;

use App\Entity\SchoolUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SchoolUnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        $choices['----'] = null;

        foreach ($options['schoolyears'] as $schoolyear) {
            $label = $schoolyear->getYearlabel();
            $choices[$label]=$schoolyear;
        }

        $builder
            ->add('unitName', TextType::class, array(
              'label' => 'Denumire unitate',
              'attr' => array('class' => 'form-control')
            ))
            ->add('startDate', DateType::class, array(
              'label' => 'Începutul activității',
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'col-6 form-control'),
            ))
            ->add('endDate', DateType::class, array(
              'label' => 'Terminarea activității',
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'col-6 form-control'),
            ))
            ->add('schoolyear', ChoiceType::class, array(
              'label' => 'An școlar',
              'choices'  => $choices,
              'attr' => array('class' => 'col-4 form-control'),
            ))
            ->add('availableSpots', NumberType::class, array(
              'label' => 'Locuri disponibile',
              'attr' => array('class' => 'col-4 form-control'),
            ))
            ->add('description', TextareaType::class, array(
              'label' => 'Descriere',
              'attr' => array('class' => 'col-4 form-control'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolUnit::class,
            'schoolyears' => array(SchoolYear::class)
        ]);

        $resolver->setRequired('schoolyears');

        //main example for validation doesn't work with arrays - workaround below
        //$resolver->setAllowedTypes('schoolyears', array( SchoolYear::class, 'int' ));

        $resolver->setAllowedTypes('schoolyears', 'array');

        // ->setAllowedValues('schoolyears', function (array $schoolyears) {
        //     // we already know it is an array as types are validated first
        //
        //     foreach ($schoolyears as $schoolyear) {
        //         if (!$schoolyear instanceof \SchoolYear) {
        //             return false;
        //         }
        //     }
        //
        //     return true;
        // });
    }
}
