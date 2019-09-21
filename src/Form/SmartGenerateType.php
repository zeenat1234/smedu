<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\MonthAccount;
use App\Entity\Student;

class SmartGenerateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('year_month', ChoiceType::class,array(
              'label'    => 'Alegere luna dorită:',
              'expanded' => false,
              'multiple' => false,
              'choices'  => $options['month_choices'],
              'choice_label' => function ($mY, $key, $value) {
                $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
                $formatter->setPattern('MMMM, YYYY');
                return strtoupper($formatter->format($mY));
              },
              'attr'  => array(
                'class' => 'form-control',
              ),
            ))
            ->add('stud_choice', ChoiceType::class,array(
              'label'    => 'Alegere elevi:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Toți' => 'all',
                'Elevi specifici...' => 'specific',
                'Toți mai puțin...' => 'excluding',
                'Elevi din unitate...' => 'unit',
              ),
              'data' => 'specific',
            ))
            ->add('unit_choice', ChoiceType::class,array(
              'label'    => 'Alegere unitate școlară:',
              'expanded' => false,
              'multiple' => false,
              'choices'  => $options['unit_choices'],
              'choice_label' => function ($schUnit, $key, $value) {
                return $schUnit->getUnitname();
              },
              'attr'  => array(
                'class' => 'form-control',
              ),
            ))
            ->add('students', EntityType::class, array(
                'class'        => Student::class,
                'choices'      => $options['students'],
                'choice_label' => 'user.getroname',
                'label'        => 'Alege elevii doriți:',
                'expanded'     => true,
                'multiple'     => true,
                'attr'         => array(
                  //'class' => 'form-check',
                ),
                'choice_attr' => function() {
                  return array('class' => '');
                },
            ))
            ->add('pay_item_type', ChoiceType::class,array(
              'label'    => 'Alege servicii generate:',
              'expanded' => true,
              'multiple' => true,
              'choices'  => array(
                'Taxă Școlară (+Penalități)' => 'tax',
                'Opționale' => 'optionals',
                'Transport' => 'transport',
                'Servicii nefacturate din urmă' => 'noninvoiced',
                'Serviciu NOU' => 'newitem',
              ),
            ))
            ->add('start_date', DateType::class, array(
              'label' => 'Prima zi din catalog',
              'required' => false,
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'form-control'),
            ))
            ->add('end_date', DateType::class, array(
              'label' => 'Ultima zi din catalog',
              'required' => false,
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'form-control'),
            ))
            ->add('itemName', TextType::class, array(
              'label' => 'Denumire Produs',
              'required' => false,
              'attr'  => array('class' => 'form-control')
            ))
            ->add('itemCount', NumberType::class, array(
              'label' => 'Cantitate',
              'data'  => 1,
              'attr' => array('class' => 'form-control'),
            ))
            ->add('itemPrice', MoneyType::class, array(
              'label'    => 'Preț/Produs',
              'currency' => 'RON',
              'scale' => 2,
              'data'  => 0,
              'attr' => array('class' => 'form-control'),
            ))
            ->add('editNote', TextareaType::class, array(
              'label' => 'Detalii Adăugare',
              'data'  => 'Adăugat cu Generare Smart',
              'attr'  => array('class' => 'form-control'),
            ))
            ->add('auto_invoice', ChoiceType::class,array(
              'label'    => 'Facturare automată:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Nu factura' => null,
                'Facturi Proforme' => 'proforma',
                'Facturi Fiscale' => 'fiscal',
              ),
            ))
            ->add('invoice_all', ChoiceType::class,array(
              'label'    => 'Tip facturare:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                '1x singură factură' => true,
                'Facturi Separate' => false,
              ),
              'data' => true,
            ))
            ->add('invoice_date', DateType::class, array(
              'label' => 'Ziua emiterii:',
              'required' => false,
              'input' => 'datetime',
              'widget' => 'single_text',
              'attr' => array('type' => 'datetime', 'class' => 'form-control'),
              'data' => new \DateTime('today'),
            ))
            ->add('invoice_discount', NumberType::class, array(
              'label' => 'Reducere',
              'data'  => 0,
              'attr' => array('class' => 'form-control'),
            ))
            ->add('save_invoice', CheckboxType::class, array(
              'label'    => 'Salvează facturile ?',
              'required' => false,
              'attr' => array('class' => 'form-check form-check-inline'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          // Configure your form options here
          'students'       => array(Student::class),
          'month_choices'  => array(DateTime::class),
          'unit_choices'   => array(SchoolUnit::class),
        ]);
    }
}
