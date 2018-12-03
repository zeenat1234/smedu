<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

use App\Entity\TransportTrip;

class TransportTripType extends AbstractType
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
            ->add('distance1', NumberType::class, array(
                'label'        => 'Distanță Dus în km:',
                'attr'         => array(
                  'class' => 'form-control',
                ),
                'empty_data' => 0,
            ))
            ->add('distance2', NumberType::class, array(
                'label'        => 'Distanță Întors în km:',
                'attr'         => array(
                  'class' => 'form-control',
                ),
                'empty_data' => 0,
            ))
            ->add('tripType', ChoiceType::class,array(
              'label'    => 'Drumuri:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Absent' => 0,
                'Dus' => 1,
                'Întors' => 2,
                'Dus/Întors' => 3,
              ),
            ))
            ->add('price', MoneyType::class, array(
              'currency' => 'RON',
              'scale' => 2,
              'label' => 'Preț:',
              'attr'  => array(
                'class' => 'form-control',
              ),
            ))
            ->add('pricePerKm', ChoiceType::class,array(
              'label'    => 'Tip taxare:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Preț pe km' => true,
                'Preț pe drum' => false,
              ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          // Configure your form options here
          'data_class' => TransportTrip::class,
        ]);
    }
}
