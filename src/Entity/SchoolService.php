<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolServiceRepository")
 */
class SchoolService
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolYear", inversedBy="schoolservices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolyear;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolUnit", inversedBy="schoolservices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolunit;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $servicename;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $servicedescription;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $serviceprice;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Enrollment", mappedBy="idService")
     */
    private $enrollments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ClassOptional", mappedBy="inServices")
     */
    private $classOptionals;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inAdvance = true;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->classOptionals = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSchoolyear(): ?SchoolYear
    {
        return $this->schoolyear;
    }

    public function setSchoolyear(?SchoolYear $schoolyear): self
    {
        $this->schoolyear = $schoolyear;

        return $this;
    }

    public function getSchoolunit(): ?SchoolUnit
    {
        return $this->schoolunit;
    }

    public function setSchoolunit(?SchoolUnit $schoolunit): self
    {
        $this->schoolunit = $schoolunit;

        return $this;
    }

    public function getServicename(): ?string
    {
        return $this->servicename;
    }

    public function setServicename(string $servicename): self
    {
        $this->servicename = $servicename;

        return $this;
    }

    public function getServicedescription(): ?string
    {
        return $this->servicedescription;
    }

    public function setServicedescription(string $servicedescription): self
    {
        $this->servicedescription = $servicedescription;

        return $this;
    }

    public function getServiceprice()
    {
        return $this->serviceprice;
    }

    public function setServiceprice($serviceprice): self
    {
        $this->serviceprice = $serviceprice;

        return $this;
    }

    /**
     * @return Collection|Enrollment[]
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(Enrollment $enrollment): self
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments[] = $enrollment;
            $enrollment->setIdService($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): self
    {
        if ($this->enrollments->contains($enrollment)) {
            $this->enrollments->removeElement($enrollment);
            // set the owning side to null (unless already changed)
            if ($enrollment->getIdService() === $this) {
                $enrollment->setIdService(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ClassOptional[]
     */
    public function getClassOptionals(): Collection
    {
        return $this->classOptionals;
    }

    public function addClassOptional(ClassOptional $classOptional): self
    {
        if (!$this->classOptionals->contains($classOptional)) {
            $this->classOptionals[] = $classOptional;
            $classOptional->addInService($this);
        }

        return $this;
    }

    public function removeClassOptional(ClassOptional $classOptional): self
    {
        if ($this->classOptionals->contains($classOptional)) {
            $this->classOptionals->removeElement($classOptional);
            $classOptional->removeInService($this);
        }

        return $this;
    }

    public function getInAdvance(): ?bool
    {
        return $this->inAdvance;
    }

    public function setInAdvance(bool $inAdvance): self
    {
        $this->inAdvance = $inAdvance;

        return $this;
    }
}
