<?php

namespace App\Form\EnrollWizard;

use App\Entity\Enrollment;
use App\Entity\SchoolUnit;
use App\Entity\SchoolService;
use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EnrollmentWizardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            // THE FOLLOWING LINES ARE FOR DEBUG PURPOSES ONLY!
            // ->add('idUnit', EntityType::class, array(
            //   'class' => SchoolUnit::class,
            //   'choice_label' => 'unitname',
            //   // 'disabled' => true,
            //   'attr' => array(
            //     'class' => 'form-control',
            //     'hidden' => true,
            //   ),
            // ))
            // ->add('idService', EntityType::class, array(
            //   'class' => SchoolService::class,
            //   'choice_label' => 'servicename',
            //   // 'disabled' => true,
            //   'attr' => array(
            //     'class' => 'form-control disabled',
            //     'hidden' => true,
            //   ),
            // ))
            ->add('notes', TextareaType::class, array(
              'label' => 'Alte mențiuni:',
              'data' => 'Nu există alte mențiuni.',
              'attr' => array('class' => 'form-control'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enrollment::class,
            'validation_groups' => array('Default'),
        ]);
    }
}
