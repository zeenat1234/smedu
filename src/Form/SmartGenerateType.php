<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
              'label'    => 'Alegere studenți:',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Toți' => 'all',
                'Studenți specifici...' => 'specific',
                'Toți mai puțin...' => 'excluding',
              ),
              'data' => 'specific',
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
        ]);
    }
}
