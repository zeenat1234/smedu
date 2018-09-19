<?php

namespace App\Form;

use App\Entity\ClassGroup;
use App\Entity\SchoolUnit;
use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ClassGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unitchoice = array();
        $unitchoice[$options['school_unit']->getUnitname()] = $options['school_unit'];

        $profChoice = array();
        foreach ($options['prof_choice'] as $prof) {
          $label = $prof->getUsername();
          $profChoice[$label] = $prof;
        }

        $builder
            ->add('schoolUnit', ChoiceType::class, array(
              'label'    => 'Unitate Școlară',
              'choices'  => $unitchoice,
              'attr'     => array(
                'class'     => 'form-control',
                'readonly'  => 'readonly',
              ),
            ))
            ->add('groupName', TextType::class, array(
              'label'  => 'Nume grupă',
              'attr'   => array(
                'class' => 'form-control',
              ),
            ))

            ->add('professor', ChoiceType::class, array(
              'label'    => 'Profesor',
              'choices'  => $profChoice,
              'attr' => array(
                'class' => 'form-control',
              ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClassGroup::class,
            'school_unit' => SchoolUnit::class,
            'prof_choice' => array(User::class),
        ]);
    }
}
