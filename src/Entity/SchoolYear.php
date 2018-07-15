<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

//Validation Classes
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolYearRepository")
 */
class SchoolYear
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $yearname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end_date;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $yearlabel;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_perm_activity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $license;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $license_status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolUnit", mappedBy="schoolyear")
     */
    private $schoolunits;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolService", mappedBy="schoolyear")
     */
    private $schoolservices;

    public function __construct()
    {
        $this->schoolunits = new ArrayCollection();
        $this->schoolservices = new ArrayCollection();
    }


    public function getId()
    {
        return $this->id;
    }

    public function getYearname(): ?string
    {
        return $this->yearname;
    }

    public function setYearname(string $yearname): self
    {
        $this->yearname = $yearname;

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

    public function getYearlabel(): ?string
    {
        return $this->yearlabel;
    }

    public function setYearlabel(string $yearlabel): self
    {
        $this->yearlabel = $yearlabel;

        return $this;
    }

    public function getIsPermActivity(): ?bool
    {
        return $this->is_perm_activity;
    }

    public function setIsPermActivity(bool $is_perm_activity): self
    {
        $this->is_perm_activity = $is_perm_activity;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(string $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getLicenseStatus(): ?string
    {
        return $this->license_status;
    }

    public function setLicenseStatus(string $license_status): self
    {
        $this->license_status = $license_status;

        return $this;
    }

    /**
     * @return Collection|SchoolUnit[]
     */
    public function getSchoolunits(): Collection
    {
        return $this->schoolunits;
    }

    public function addSchoolunit(SchoolUnit $schoolunit): self
    {
        if (!$this->schoolunits->contains($schoolunit)) {
            $this->schoolunits[] = $schoolunit;
            $schoolunit->setSchoolyear($this);
        }

        return $this;
    }

    public function removeSchoolunit(SchoolUnit $schoolunit): self
    {
        if ($this->schoolunits->contains($schoolunit)) {
            $this->schoolunits->removeElement($schoolunit);
            // set the owning side to null (unless already changed)
            if ($schoolunit->getSchoolyear() === $this) {
                $schoolunit->setSchoolyear(null);
            }
        }

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
            $schoolservice->setSchoolyear($this);
        }

        return $this;
    }

    public function removeSchoolservice(SchoolService $schoolservice): self
    {
        if ($this->schoolservices->contains($schoolservice)) {
            $this->schoolservices->removeElement($schoolservice);
            // set the owning side to null (unless already changed)
            if ($schoolservice->getSchoolyear() === $this) {
                $schoolservice->setSchoolyear(null);
            }
        }

        return $this;
    }

}
