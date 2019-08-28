<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

use App\Entity\TransportRoute;

class SYStep8Type extends AbstractType
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
            ->add('isImport', ChoiceType::class, array(
              'label' => 'Denumire unitate',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Omite' => 0,
                'Import' => 1,
              ),
              'data' => 1,
              'mapped' => false,
            ))
            ->add('distance', NumberType::class, array(
                'label'        => 'Distanță în km:',
                'attr'         => array(
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
            ->add('price', MoneyType::class, array(
                'currency' => 'RON',
                'scale' => 2,
                'label' => 'Preț:',
                'attr'  => array(
                  'class' => 'form-control',
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransportRoute::class,
        ]);
    }
}
