<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

// use App\Entity\MonthAccount;
use App\Entity\Student;

class RouteCreationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('price', MoneyType::class, array(
              'label'    => 'Preț',
              'currency' => 'RON',
              'scale' => 2,
              //'mapped' => false,
              'attr' => array('class' => 'form-control'),
              'data' => 0.00,
              'empty_data' => 0.00,
            ))
            ->add('pricePerKm', ChoiceType::class,array(
              'label'    => 'Tip taxare:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Preț pe km' => true,
                'Preț pe drum' => false,
              ),
              'data' => true,
            ))
            ->add('students', EntityType::class, array(
                'class'        => Student::class,
                'choices'      => $options['students'],
                'choice_label' => 'user.getroname',
                'label'        => 'Alege elevii:',
                'expanded'     => true,
                'multiple'     => true,
                'attr'         => array(
                  //'class' => 'form-check',
                ),
                'choice_attr' => function() {
                  return array('class' => '');
                },
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          // Configure your form options here
          'students' => array(Student::class),
        ]);
    }
}
