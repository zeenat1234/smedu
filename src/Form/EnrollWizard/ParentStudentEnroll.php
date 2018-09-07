<?php

namespace App\Form\EnrollWizard;

use App\Entity\Enrollment;
use App\Entity\User;
use App\Entity\SchoolYear;
use App\Entity\SchoolUnit;
use App\Entity\SchoolService;

use Symfony\Component\Validator\Constraints as Assert;

class ParentStudentEnroll {

  /**
	 * @Assert\Type(type="App\Entity\Enrollment", groups={"flow_parentStudentEnroll_step8"})
	 * @Assert\Valid()
	 */
	public $enrollment;

	/**
	 * @var bool
	 * @Assert\NotNull(groups={"flow_parentStudentEnroll_step1"})
	 * @Assert\Type(type="boolean", groups={"flow_parentStudentEnroll_step1"})
	 */
	public $addGuardian = true;

  /**
	 * @Assert\Type(type="App\Entity\User", groups={"flow_parentStudentEnroll_step2"})
	 */
  public $guardian;

	public function getGuardian(): ?User
	{
			return $this->guardian;
	}

	/**
	 * @Assert\Type(type="App\Entity\User", groups={"flow_parentStudentEnroll_step2"})
	 * @Assert\Valid()
	 */
  public $newGuardian = NULL;

	/**
	 * @var bool
	 * @Assert\NotNull(groups={"flow_parentStudentEnroll_step3"})
	 * @Assert\Type(type="boolean", groups={"flow_parentStudentEnroll_step3"})
	 */
	public $addStudent = true;

  /**
	 * @Assert\Type(type="App\Entity\User", groups={"flow_enrollStudent_step4"})
	 * @Assert\Valid()
	 */
  public $student;

	/**
	 * @Assert\Type(type="App\Entity\User", groups={"flow_enrollStudent_step4"})
	 * @Assert\Valid()
	 */
  public $newStudent = NULL;

	/**
	 * @Assert\Valid()
	 */
  public $schoolYear;

	public function getSchoolYear(): ?SchoolYear
	{
			return $this->schoolYear;
	}

	/**
	 * @Assert\Valid()
	 */
  public $schoolUnit;

	public function getSchoolUnit(): ?SchoolUnit
	{
			return $this->schoolUnit;
	}

	/**
	 * @Assert\Valid()
	 */
  public $schoolService;

	public function getSchoolService(): ?SchoolService
	{
			return $this->schoolService;
	}

	public function __construct() {
		$this->enrollment = new Enrollment();
	}
}
