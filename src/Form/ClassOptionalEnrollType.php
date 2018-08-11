<?php

namespace App\Form;

use App\Entity\ClassOptional;
use App\Entity\Student;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ClassOptionalEnrollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('students', EntityType::class, [
              'class'        => Student::class,
              'choices'      => $options['students'],
              'choice_label' => 'user.getfullname',
              'label'        => 'Inscrieri elevi',
              'expanded'     => true,
              'multiple'     => true,
          ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClassOptional::class,
            'students'   => array(Student::class),
        ]);
    }
}
