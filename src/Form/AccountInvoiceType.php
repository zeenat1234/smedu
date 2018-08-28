<?php

namespace App\Form;

use App\Entity\AccountInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class AccountInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoicePaid', MoneyType::class, array(
              'label'    => 'Total Achitat',
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array('class' => 'col-3 form-control'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccountInvoice::class,
        ]);
    }
}
