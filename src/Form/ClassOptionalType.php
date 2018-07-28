<?php

namespace App\Form;

use App\Entity\ClassOptional;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassOptionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('optionalName')
            ->add('description')
            ->add('price')
            ->add('schoolUnit')
            ->add('inServices')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClassOptional::class,
        ]);
    }
}
