<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolUnitRepository")
 */
class SchoolUnit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $unitname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end_date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolYear", inversedBy="schoolunits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolyear;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolService", mappedBy="schoolunit")
     */
    private $schoolservices;

    public function __construct()
    {
        $this->schoolservices = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUnitname(): ?string
    {
        return $this->unitname;
    }

    public function setUnitname(string $unitname): self
    {
        $this->unitname = $unitname;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

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

    public function getSchoolyear(): ?SchoolYear
    {
        return $this->schoolyear;
    }

    public function setSchoolyear(?SchoolYear $schoolyear): self
    {
        $this->schoolyear = $schoolyear;

        return $this;
    }

    /**
     * @return Collection|SchoolService[]
     */
    public function getSchoolservices(): Collection
    {
        return $this->schoolservices;
    }

    public function addSchoolservice(SchoolService $schoolservice): self
    {
        if (!$this->schoolservices->contains($schoolservice)) {
            $this->schoolservices[] = $schoolservice;
            $schoolservice->setSchoolunit($this);
        }

        return $this;
    }

    public function removeSchoolservice(SchoolService $schoolservice): self
    {
        if ($this->schoolservices->contains($schoolservice)) {
            $this->schoolservices->removeElement($schoolservice);
            // set the owning side to null (unless already changed)
            if ($schoolservice->getSchoolunit() === $this) {
                $schoolservice->setSchoolunit(null);
            }
        }

        return $this;
    }

}