<?php

namespace App\Form\EnrollWizard;

use App\Entity\User;
use App\Entity\SchoolYear;
use App\Entity\SchoolUnit;
use App\Entity\SchoolService;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\EnrollWizard\UserParentType;
use App\Form\EnrollWizard\UserChildType;

#this is used to validate cascaded formType
use Symfony\Component\Validator\Constraints\Valid;

class ParentStudentEnrollForm extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

    $useFqcn = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix'); // Symfony's Form component >=2.8

    switch ($options['flow_step']) {
			case 1:
				$builder->add('addGuardian', ChoiceType::class, array(
					'label' => 'Adaugă părinte nou?',
					'expanded' => true,
					'choices'  => array(
						'Da' => true,
						'Nu' => false,
					),
				))
				->add('guardian', EntityType::class, array(
						'label' => 'Părinte existent',
				    'class' => User::class,
				    'query_builder' => function (EntityRepository $er) {
				        return $er->createQueryBuilder('u')
										->andWhere('u.usertype = :searchTerm')
										->setParameter('searchTerm', 'ROLE_PARENT')
				            ->orderBy('u.lastName', 'ASC');
				    },
				    'choice_label' => 'getRoName',
				))
				;
				break;

			case 2:
				// $user = $options['data']->newGuardian;
				// $user->setPassword('temporary');
				$builder->add('newGuardian', UserParentType::class, array(
					'label' => ' ',
					// 'data' => $user,
					'constraints' => new Valid(),
				));
				break;

			case 3:
				$builder->add('addStudent', ChoiceType::class, array(
					'label' => 'Adaugă elev nou?',
					'expanded' => true,
					'choices'  => array(
						'Da' => true,
						'Nu' => false,
					),
				))
				;
				break;

			case 4:
				if ($options['data']->addStudent == true) {
					$builder->add('newStudent', UserChildType::class, array(
						'label' => ' ',
						'constraints' => array(new Valid()),
					));
				}	else {

					$theGuardian = $options['guardian'];

					$builder->add('student', EntityType::class, array(
							'label' => 'Elev existent',
					    'class' => User::class,
					    'query_builder' => function (EntityRepository $er) use ($theGuardian) {
					        return $er->createQueryBuilder('u')
											->andWhere('u.usertype = :searchTerm')
											->andWhere('u.guardian = :searchGuardian')
											->setParameter('searchTerm', 'ROLE_PUPIL')
											->setParameter('searchGuardian', $theGuardian->getGuardianacc()->getId())
					            ->orderBy('u.firstName', 'ASC');
					    },
					    'choice_label' => 'getRoName',
					));
				}
				break;

			case 5:
				$yearChoice = array();

				foreach ($options['school_years'] as $schoolyear) {
					$label = $schoolyear->getYearname();
					$yearChoice[$label] = $schoolyear;
				}

				$builder->add('schoolYear', ChoiceType::class, array(
					'label' => 'Anul Școlar dorit:',
					'expanded' => true,
					'choices'  => $yearChoice,
				));
				break;

			case 6:
				$unitChoice = array();

				foreach ($options['school_units'] as $schoolunit) {
					$label = $schoolunit->getUnitname();
					$unitChoice[$label] = $schoolunit;
				}

				$builder->add('schoolUnit', ChoiceType::class, array(
					'label' => 'Unitatea Școlară dorită:',
					'expanded' => true,
					'choices'  => $unitChoice,
				));
				break;

			case 7:
				$servicesChoice = array();

				foreach ($options['school_services'] as $schoolservice) {
					$label = $schoolservice->getServicename();
					$servicesChoice[$label] = $schoolservice;
				}

				$builder->add('schoolService', ChoiceType::class, array(
					'label' => 'Serviciul Școlar dorit:',
					'expanded' => true,
					'choices'  => $servicesChoice,
				));
				break;

			case 8:
				$enrollment = $options['data']->enrollment;

				if ($options['data']->addGuardian) {
					$enrollment->setIdParent($options['data']->newGuardian);
				} else {
					$enrollment->setIdParent($options['data']->guardian);
				}

				if ($options['data']->addStudent) {
					$enrollment->setIdChild($options['data']->newStudent);
				} else {
					$enrollment->setIdChild($options['data']->student);
				}

				$enrollment->setIdUnit($options['data']->schoolUnit);
				$enrollment->setIdService($options['data']->schoolService);

				$enrollment->setEnrollDate(new \DateTime('now'));
        $enrollment->setIsActive(true);
        $enrollment->setSchoolYear($options['data']->schoolUnit->getSchoolYear());

				$builder->add('enrollment', EnrollmentWizardType::class, array(
					'label' => ' ',
					'data' => $enrollment,
					'constraints' => new Valid(),
				));
				break;
			}


	}

  /**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
  {
		$resolver->setDefaults(array(
			'data_class' => 'App\Form\EnrollWizard\ParentStudentEnroll',
			'school_years' => array(SchoolYear::class),
			'school_units' => array(SchoolUnit::class),
			'school_services' => array(SchoolService::class),
			'guardian' => User::class,
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
  {
		return $this->getBlockPrefix();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlockPrefix()
  {
		return 'parentStudentEnroll';
	}

}
