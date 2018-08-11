<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClassOptionalRepository")
 */
class ClassOptional
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $optionalName;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolUnit", inversedBy="classOptionals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolUnit;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SchoolService", inversedBy="classOptionals")
     */
    private $inServices;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Student", inversedBy="ClassOptionals")
     */
    private $students;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionalSchedule", mappedBy="classOptional", orphanRemoval=true)
     */
    private $optionalSchedules;

    public function __construct()
    {
        $this->inServices = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->optionalSchedules = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOptionalName(): ?string
    {
        return $this->optionalName;
    }

    public function setOptionalName(string $optionalName): self
    {
        $this->optionalName = $optionalName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSchoolUnit(): ?SchoolUnit
    {
        return $this->schoolUnit;
    }

    public function setSchoolUnit(?SchoolUnit $schoolUnit): self
    {
        $this->schoolUnit = $schoolUnit;

        return $this;
    }

    /**
     * @return Collection|SchoolService[]
     */
    public function getInServices(): Collection
    {
        return $this->inServices;
    }

    public function addInService(SchoolService $inService): self
    {
        if (!$this->inServices->contains($inService)) {
            $this->inServices[] = $inService;
        }

        return $this;
    }

    public function removeInService(SchoolService $inService): self
    {
        if ($this->inServices->contains($inService)) {
            $this->inServices->removeElement($inService);
        }

        return $this;
    }

    /**
     * @return Collection|Student[]
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->addClassOptional($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
            $student->removeClassOptional($this);
        }

        return $this;
    }

    /**
     * @return Collection|OptionalSchedule[]
     */
    public function getOptionalSchedules(): Collection
    {
        return $this->optionalSchedules;
    }

    public function addOptionalSchedule(OptionalSchedule $optionalSchedule): self
    {
        if (!$this->optionalSchedules->contains($optionalSchedule)) {
            $this->optionalSchedules[] = $optionalSchedule;
            $optionalSchedule->setClassOptional($this);
        }

        return $this;
    }

    public function removeOptionalSchedule(OptionalSchedule $optionalSchedule): self
    {
        if ($this->optionalSchedules->contains($optionalSchedule)) {
            $this->optionalSchedules->removeElement($optionalSchedule);
            // set the owning side to null (unless already changed)
            if ($optionalSchedule->getClassOptional() === $this) {
                $optionalSchedule->setClassOptional(null);
            }
        }

        return $this;
    }
}
