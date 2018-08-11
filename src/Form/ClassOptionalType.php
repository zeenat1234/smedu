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

class ClassOptionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unitchoice = array();
        $unitchoice[$options['school_unit']->getUnitname()] = $options['school_unit'];

        $builder
            ->add('optionalName', TextType::class, array(
              'attr' => array('class' => 'col-6 form-control')
            ))
            ->add('description', TextareaType::class, array(
              'attr' => array('class' => 'col-6 form-control'),
            ))
            ->add('price', MoneyType::class, array(
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array('class' => 'col-3 form-control'),
            ))
            ->add('schoolUnit', ChoiceType::class, array(
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
        ]);
    }
}
