<?php

namespace App\Form;

use App\Entity\Payment;
use App\Entity\AccountInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SmartPayType extends AbstractType
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

        // $invoices = array();
        //
        // if ($options['invoices']->count() != 0) {
        //   foreach ($options['invoices'] as $invoice) {
        //     $label = $invoice->getInvoiceName();
        //     $invoices[$label] = $invoice;
        //   }
        // } else {
        //   $invoices['nu există facturi neplătite'] = $invoice;
        // }


        $builder
            ->add('payMethod', ChoiceType::class,array(
              'label'    => 'Metodă de plată',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                '1x Factură (integral)' => 'single',
                '1x Factură (parțial)' => 'partial',
                'Facturi multiple (integral)' => 'multiple',
                'Facturi multiple (parțial)' => 'multiple_partial'
              ),
            ))
            ->add('payAmount', MoneyType::class, array(
              'label'    => 'Total Achitat',
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array(
                'class' => 'form-control currency',
                'placeholder' => '0,00'
              ),
            ))
            ->add('payInvoices', EntityType::class, array(
                'class'        => AccountInvoice::class,
                'choices' => $options['invoices'],
                'choice_label' => 'invoiceName',
                'label' => 'Facturi selectate',
                'expanded'     => true,
                'multiple'     => true,
                'attr'         => array(),
                'choice_attr' => function() {
                  return array('class' => '');
                },
            ))
            ->add('payProof', FileType::class, array(
              'multiple' => true,
              'mapped' => false,
              'label' => 'Dovadă de plată (PDF, JPG, JPEG, PNG)',
              'data_class' => null
            ))
            ->add('addAdvance', ChoiceType::class,array(
              'mapped' => false,
              'label'    => 'Adaugă avans?',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Nu' => false,
                'Da' => true,
              ),
              'data' => false,
            ))
            ->add('payAdvance', MoneyType::class, array(
              'label'    => 'Avans',
              'currency' => 'RON',
              'scale' => 2,
              'attr' => array('class' => 'form-control'),
              'attr' => array(
                'class' => 'form-control currency',
                'placeholder' => '0,00'
              ),
              'data' => 0,
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
          $data = $event->getData();
          $form = $event->getForm();

          $data['payAmount'] = floatVal(str_replace(',','.',str_replace('.','',($data['payAmount']))));
          $data['payAdvance'] = floatVal(str_replace(',','.',str_replace('.','',($data['payAdvance']))));

          $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'invoices' => array(AccountInvoice::class),
            //'validation_groups' => array('receiptDetails'),
        ]);
    }
}
