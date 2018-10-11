<?php

namespace App\Form;

use App\Entity\AccountInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UserMyaccountInvoiceType extends AbstractType
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
            ->add('invoicePaid', MoneyType::class, array(
              'label'    => 'Total Achitat',
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array('class' => 'form-control'),
            ))
            ->add('payProof', FileType::class, array(
              //'data'  => null,
              'label' => 'Dovadă de plată (format PDF, JPG, JPEG, PNG)',
              'data_class' => null
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccountInvoice::class,
            'validation_groups' => array('invoiceUpload'),
        ]);
    }
}
