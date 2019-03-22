<?php

namespace App\Form;

use App\Entity\Enrollment;
use App\Entity\SchoolUnit;
use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\HiddenType;
//use Symfony\Component\Intl\NumberFormatter\NumberFormatter; //TODO: See if needed or not...

class EnrollmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unitchoice = array();
        $unitchoice[$options['school_unit']->getUnitname()] = $options['school_unit'];

        $servicechoices = array();
        foreach ($options['school_unit']->getSchoolservices() as $schoolService) {
          $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
          $label = $schoolService->getServicename().' -- '.$formatter->formatCurrency($schoolService->getServiceprice(), 'RON');
          $servicechoices[$label] = $schoolService;
        }

        $parents = array();
        foreach ($options['parents'] as $parent) {
          $label = $parent->getFullName(1);
          $parents[$label] = $parent;
        }

        $children = array();
        foreach ($options['children'] as $child) {
          $label = $child->getFullName(1);
          $children[$label] = $child;
        }

        $builder
            ->add('idUnit', ChoiceType::class, array(
              'label' => 'Unitate școlară',
              'choices'  => $unitchoice,
              'attr' => array(
                'class' => 'form-control',
                'readonly' => 'readonly',
              ),
            ))
            ->add('idService', ChoiceType::class, array(
              'label' => 'Serviciu școlar',
              'choices'  => $servicechoices,
              'attr' => array(
                'class' => 'form-control',
              ),
            ))
            ->add('idParent', ChoiceType::class, array(
              'label' => 'Părinte',
              'choices'  => $parents,
              'attr' => array(
                'class' => 'form-control',
              ),
            ))
            ->add('idChild', ChoiceType::class, array(
              'label' => 'Elev',
              'choices'  => $children,
              'attr' => array(
                'class' => 'form-control',
              ),
            ))
            ->add('notes', TextareaType::class, array(
              'label' => 'Notă adițională',
              'attr' => array('class' => 'form-control'),
            ))
            ->add('daysToPay', NumberType::class, array(
              'label' => 'Termen de Plată în zile',
              'attr' => array('class' => 'form-control'),
            ))
            // ->add('enrollDate', HiddenType::class, array(
            //   'input' => 'datetime',
            //   'widget' => 'single_text',
            //   'attr' => array('type' => 'datetime', 'class' => 'form-control'),
            // ))

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enrollment::class,
            'school_unit' => SchoolUnit::class,
            'parents' => array(User::class),
            'children' => array(User::class),
        ]);
    }
}
