<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\AccountPermission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AccountPermissionType extends AbstractType
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
          ->add('accountPermissions', EntityType::class, [
              'class'        => AccountPermission::class,
              //'choices'      => $options['permissions'],
              'choice_label' => 'label',
              'label'        => 'Permisiuni',
              'expanded'     => true,
              'multiple'     => true,
              'attr'         => array(
                //'class' => 'form-check',
                //'id' => 'form_categories',
              ),
              'choice_attr' => function() {
                return array('class' => '');
              },
              //'label_attr' => array('class' => 'form-check-label'),
              // 'choice_attr' => array(
              //   '0' => array('class' => 'form-check-input'),
              // ),

          ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            //'permissions'   => array(AccountPermission::class),
        ]);
    }
}
