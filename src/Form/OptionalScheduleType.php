<?php

namespace App\Form;

use App\Entity\OptionalSchedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class OptionalScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['scheduled_time'] === null) {
            $value = '';
        } else {
            $value = $options['scheduled_time'];
        }
        $builder
            ->add('scheduledDateTime', DateTimeType::class, array(
              'label'    => 'Data și Ora',
              'widget' => 'single_text',
              //'html5' => true,
              'input' => 'datetime',
              //'format' => 'yyyy-MM-dd',
              'attr' => array(
                'type' => 'datetime-local',
                'class' => 'form-control',
                'value' => $value,
              ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OptionalSchedule::class,
            'scheduled_time' => null,
        ]);
    }
}
