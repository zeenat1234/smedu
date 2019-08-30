<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use App\Entity\Enrollment;
use App\Entity\SchoolUnit;

use App\Repository\ClassGroupRepository;


class SYStep6Type extends AbstractType
{
    private $formCount;

    public function __construct(ClassGroupRepository $cgrepo)
    {
        $this->formCount = 0;
        $this->cgrepo = $cgrepo;
    }

    public function getBlockPrefix()
    {
        return parent::getBlockPrefix().'_'.$this->formCount;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        ++$this->formCount;

        $builder
            ->add('isImport', ChoiceType::class, array(
              'label' => 'Denumire unitate',
              'expanded' => true,
              'multiple' => false,
              'choices'  => array(
                'Omite' => 0,
                'Import' => 1,
              ),
              'data' => 0,
              'mapped' => false,
            ))
            ->add('daysToPay', NumberType::class, array(
              'label' => 'Termen de Plată în zile',
              'attr' => array('class' => 'form-control'),
            ))
            ->add('notes', TextareaType::class, array(
              'label' => 'Notă adițională',
              'attr' => array('class' => 'form-control'),
            ))
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function ($event) {

            $builder = $event->getForm();
            $enrollment = $builder->getData();

            $servicechoices = array();

            foreach ($enrollment->getIdUnit()->getSchoolServices() as $schoolService) {
              $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
              $label = $schoolService->getServicename().' -- '.$formatter->formatCurrency($schoolService->getServiceprice(), 'RON');
              $servicechoices[$label] = $schoolService;
            }

            $classgroupchoices = array();

            foreach ($enrollment->getIdUnit()->getClassGroups() as $classGroup) {
              $label = $classGroup->getGroupName();
              $classgroupchoices[$label] = $classGroup;
            }

            // The following checks if students have actually been enrolled in a classgroup before mapping
            if ($enrollment->getStudent()->getImportedFrom()->getClassGroup() != null) {
              $newGroup = $this->cgrepo->findOneBy(array(
                'importedFrom' => $enrollment->getStudent()->getImportedFrom()->getClassGroup()->getId(),
              ));
            } else {
              $newGroup = reset($classgroupchoices);
            }

            $builder
              ->add('idService', ChoiceType::class, array(
                'label' => 'Serviciu școlar',
                'choices'  => $servicechoices,
                'attr' => array(
                  'class' => 'form-control',
                ),
              ))
              ->add('importClassGroup', ChoiceType::class, array(
                'label' => 'Grupă',
                'choices'  => $classgroupchoices,
                'attr' => array(
                  'class' => 'form-control',
                ),
                'data' => $newGroup,
                //'mapped' => false,
              ))
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enrollment::class,
        ]);

    }
}
