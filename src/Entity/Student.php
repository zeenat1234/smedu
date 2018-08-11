<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentRepository")
 */
class Student
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="students")
     * @ORM\JoinColumn(nullable=false)
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassGroup", inversedBy="students")
     */
    private $classGroup;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ClassOptional", mappedBy="students")
     */
    private $ClassOptionals;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolUnit", inversedBy="students")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolUnit;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Enrollment", mappedBy="student", cascade={"persist", "remove"})
     */
    private $enrollment;

    public function __construct()
    {
        $this->ClassOptionals = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getClassGroup(): ?ClassGroup
    {
        return $this->classGroup;
    }

    public function setClassGroup(?ClassGroup $classGroup): self
    {
        $this->classGroup = $classGroup;

        return $this;
    }

    /**
     * @return Collection|ClassOptional[]
     */
    public function getClassOptionals(): Collection
    {
        return $this->ClassOptionals;
    }

    public function addClassOptional(ClassOptional $classOptional): self
    {
        if (!$this->ClassOptionals->contains($classOptional)) {
            $this->ClassOptionals[] = $classOptional;
        }

        return $this;
    }

    public function removeClassOptional(ClassOptional $classOptional): self
    {
        if ($this->ClassOptionals->contains($classOptional)) {
            $this->ClassOptionals->removeElement($classOptional);
        }

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

    public function getEnrollment(): ?Enrollment
    {
        return $this->enrollment;
    }

    public function setEnrollment(Enrollment $enrollment): self
    {
        $this->enrollment = $enrollment;

        // set the owning side of the relation if necessary
        if ($this !== $enrollment->getStudent()) {
            $enrollment->setStudent($this);
        }

        return $this;
    }
}
