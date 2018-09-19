<?php

namespace App\Form;

use App\Entity\ClassOptional;
use App\Entity\Student;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserMyaccountEnrollType extends AbstractType
{
    private $formCount;

    public function __construct()
    {
        $this->formCount = 0;
    }

    public function getBlockPrefix()
    {
        return parent::getBlockPrefix().'_'.$this->formCount;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        ++$this->formCount;

        $builder
          ->add('classOptionals', EntityType::class, [
              'class'        => ClassOptional::class,
              'choices'      => $options['optionals'],
              'choice_label' => 'optionalname',
              'label'        => 'Ofertă opționale: ',
              'expanded'     => true,
              'multiple'     => true,
              'attr'         => array(
                //'class' => 'form-check',
                //'id' => 'form_categories',
              ),
              'choice_attr' => function() {
                return array('class' => ClassOptional::class);
              },
              //'label_attr' => array('class' => 'form-check-label'),
              // 'choice_attr' => array(
              //   '0' => array('class' => 'form-check-input'),
              // ),

          ])
          // ->add('submit', SubmitType::class, [
          //       'label' => 'Actualizează',
          //       'attr' => [
          //           'class' => 'btn-sm btn-success'
          //       ]
          // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
            'optionals'   => array(ClassOptional::class),
        ]);
    }
}
