<?php

namespace App\Form\EnrollWizard;

use App\Form\EnrollWizard\ParentStudentEnroll;
use App\Form\EnrollWizard\ParentStudentEnrollForm;

// use Craue\FormFlowBundle\Event\PreBindEvent;
use Craue\FormFlowBundle\Event\PostBindSavedDataEvent;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Craue\FormFlowBundle\Form\FormFlowInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParentStudentEnrollFlow extends FormFlow implements EventSubscriberInterface {

  /**
	 * {@inheritDoc}
	 */
	public function setEventDispatcher(EventDispatcherInterface $dispatcher) {
		parent::setEventDispatcher($dispatcher);
		$dispatcher->addSubscriber($this);
	}
	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents() {
		return array(
			// FormFlowEvents::PRE_BIND => 'onPreBind',
			FormFlowEvents::POST_BIND_SAVED_DATA => 'onPostBindSavedData',
		);
	}

	public function onPostBindSavedData(PostBindSavedDataEvent $event) {
		// if ($event->getStepNumber() === 2) {
		// 	$formData = $event->getFormData();
		// 	if ( !empty($formData->guardian) ) {
		// 	// 	$formData->addIdParent($formData->idParent);
		// 	// 	$formData->driver->vehicles->add($formData->vehicle);
		// 	}
		// 	$formData = $event->getFormData();
		// 	$formData->newGuardian->setUsername(trim($formData->newGuardian->getFirstName().'.'.$formData->newGuardian->getLastName()));
		// }
	}

	//overriden for dynamic options based on steps
	/**
	 * {@inheritDoc}
	 */
	public function getFormOptions($step, array $options = array()) {

		$options = parent::getFormOptions($step, $options);

		$formData = $this->getFormData();
		$guardian = $formData->getGuardian();

		if ($step === 4) {
				$options['guardian'] = $guardian;
		}
		if ($step === 6) {
				$units = $formData->getSchoolYear()->getSchoolunits();
				$options['school_units'] = $units;
		}
		if ($step === 7) {
				$services = $formData->getSchoolUnit()->getSchoolservices();
				$options['school_services'] = $services;
		}
		return $options;
	}

  protected $allowDynamicStepNavigation = true;

	protected function loadStepsConfig() {

    //$formType = 'App\Form\ParentStudentEnrollForm';
    $useFqcn = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix'); // Symfony's Form component >=2.8
		$formType = $useFqcn ? 'App\Form\EnrollWizard\ParentStudentEnrollForm' : 'parentStudentEnroll';

		return array(
      array(
				'label' => 'Părinte Existent?',
				'form_type' => $formType,
			),
			array(
				'label' => 'Adaugă Părinte',
				'form_type' => $formType,
				'form_options' => array(
					'validation_groups' => array('Default'),
				),
        'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
					return $estimatedCurrentStepNumber > 1 && !$flow->getFormData()->addGuardian;
				},
			),
			array(
				'label' => 'Elev Existent?',
				'form_type' => $formType,
				'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
					return $estimatedCurrentStepNumber > 2 && $flow->getFormData()->addGuardian;
				},
			),
			array(
				'label' => 'Adaugă Elev',
				'form_type' => $formType,
				// don't skip this step - adjust depending on answer from step 3
				// 'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
				// 	return $estimatedCurrentStepNumber > 3 && !$flow->getFormData()->addStudent;
				// },
			),
			array(
				'label' => 'An Școlar',
				'form_type' => $formType,
			),
			array(
				'label' => 'Unitate Școlară',
				'form_type' => $formType,
			),
			array(
				'label' => 'Serviciu Dorit',
				'form_type' => $formType,
			),
			array(
				'label' => 'Confirmare Înscriere',
				'form_type' => $formType,
			),
		);
	}

}
