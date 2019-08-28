<?php

namespace App\Form;

use App\Entity\ClassOptional;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SYStep7Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('field_name')
            ->add('classOptionals', EntityType::class, [
                'class'        => ClassOptional::class,
                'choices'      => $options['classoptionals'],
                'choice_label' => 'optionalname',
                'label'        => 'Opționale (import înscrieri)',
                'expanded'     => true,
                'multiple'     => true,
                'attr'         => array(
                  //'class' => 'form-check',
                  //'id' => 'form_categories',
                ),
                'choice_attr' => function() {
                  return array('class' => '');
                },
                //'label_attr' => array('class' => 'form-check-label'),
                // 'choice_attr' => array(
                //   '0' => array('class' => 'form-check-input'),
                // ),

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'classoptionals'   => array(ClassOptional::class),
        ]);
    }
}
