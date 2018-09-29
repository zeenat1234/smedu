<?php

namespace App\Form;

use App\Entity\OptionalSchedule;
use App\Entity\OptionalsAttendance;
use App\Entity\Student;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OptionalsAttendanceType extends AbstractType
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
        ->add('hasAttended', ChoiceType::class, array(
            'label' => false,
            'choices' => array(
                'Prezent' => true,
                'Absent' => false,
            ),
            'expanded' => true,
            'multiple' => false,
        ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OptionalsAttendance::class,
        ]);
    }
}
