<?php

namespace App\Form;

use App\Entity\ClassGroup;
use App\Entity\Student;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ClassGroupEnrollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('students', EntityType::class, [
              'class'        => Student::class,
              'choices'      => $options['students'],
              'choice_label' => 'user.getroname',
              'label'        => 'Inscrieri elevi',
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
            'data_class' => ClassGroup::class,
            'students'   => array(Student::class),
        ]);
    }
}
