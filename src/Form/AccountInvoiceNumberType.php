<?php

namespace App\Form;

use App\Entity\AccountInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AccountInvoiceNumberType extends AbstractType
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
            ->add('invoiceSerial', TextType::class, array(
              'label'    => 'Serie',
              'attr' => array('class' => 'form-control'),
            ))
            ->add('invoiceNumber', NumberType::class, array(
              'label' => 'Număr',
              'attr' => array('class' => 'form-control'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccountInvoice::class,
            'validation_groups' => array('invoiceDetails'),
        ]);
    }
}
