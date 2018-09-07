<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OptionalsAttendanceRepository")
 * @UniqueEntity(
 *     fields={"classOptional", "optionalSchedule", "student"},
 *     message="Duplicate entry detected!!! Contact a webmaster and reference: Bug#1001C"
 * )
 */
class OptionalsAttendance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassOptional", inversedBy="optionalsAttendances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classOptional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OptionalSchedule", inversedBy="optionalsAttendances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $optionalSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="optionalsAttendances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasAttended;

    public function getId()
    {
        return $this->id;
    }

    public function getClassOptional(): ?ClassOptional
    {
        return $this->classOptional;
    }

    public function setClassOptional(?ClassOptional $classOptional): self
    {
        $this->classOptional = $classOptional;

        return $this;
    }

    public function getOptionalSchedule(): ?OptionalSchedule
    {
        return $this->optionalSchedule;
    }

    public function setOptionalSchedule(?OptionalSchedule $optionalSchedule): self
    {
        $this->optionalSchedule = $optionalSchedule;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getHasAttended(): ?bool
    {
        return $this->hasAttended;
    }

    public function setHasAttended(bool $hasAttended): self
    {
        $this->hasAttended = $hasAttended;

        return $this;
    }
}
