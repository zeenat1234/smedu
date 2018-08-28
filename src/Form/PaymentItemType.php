<?php

namespace App\Form;

use App\Entity\PaymentItem;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PaymentItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemName', TextType::class, array(
              'label'=> 'Denumire Produs',
              'attr' => array('class' => 'form-control')
            ))
            ->add('itemCount', NumberType::class, array(
              'label' => 'Cantitate',
              'attr' => array('class' => 'form-control'),
            ))
            ->add('itemPrice', MoneyType::class, array(
              'label'    => 'Preț/Produs',
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array('class' => 'form-control'),
            ))
            ->add('editNote', TextareaType::class, array(
              'label'    => 'Motiv Adăugare/Modificare',
              'attr' => array('class' => 'form-control'),
            ))
            //TODO Add posibility to move to another month account??
            //->add('monthAccount')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentItem::class,
        ]);
    }
}
