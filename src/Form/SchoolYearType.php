<?php

namespace App\Form;

use App\Entity\SchoolYear;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

# Symfony4 best practice is to not use a submit type in the formType or Controller
#use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SchoolYearType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('yearLabel', TextType::class, array(
              'attr' => array('class' => 'form-control'),
              'required' => true,
            ))
            ->add('startDate', DateType::class, array(
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'form-control'),
            ))
            ->add('endDate', DateType::class, array(
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'form-control'),
            ))
            ->add('is_perm_activity', CheckboxType::class, array(
              'label'    => 'Activitate Permanentă ?',
              'required' => false,
              'attr' => array('class' => 'form-check form-check-inline'),
            ))
            ->add('license', TextType::class, array(
              'label'    => 'Licență',
              'attr' => array('class' => 'form-control'),
            ))
            ->add('license_status', TextType::class, array(
              'label'    => 'Status licență',
              'attr' => array('class' => 'form-control'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolYear::class,
        ]);
    }
}
