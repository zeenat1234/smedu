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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Enrollment", mappedBy="schoolYear")
     */
    private $enrollments;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSetup1 = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSetup2 = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSetup3 = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSetup4 = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSetup5 = 0;

    /**
    * @ORM\Column(type="boolean")
    */
    private $isSetup6 = 0;
    /**
    * @ORM\Column(type="boolean")
    */
    private $isSetup7 = 0;

    /**
    * @ORM\Column(type="boolean")
    */
    private $isSetup8 = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSetupComplete = 0;


    public function __construct()
    {
        $this->schoolunits = new ArrayCollection();
        $this->schoolservices = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
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
            $enrollment->setSchoolYear($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): self
    {
        if ($this->enrollments->contains($enrollment)) {
            $this->enrollments->removeElement($enrollment);
            // set the owning side to null (unless already changed)
            if ($enrollment->getSchoolYear() === $this) {
                $enrollment->setSchoolYear(null);
            }
        }

        return $this;
    }

    public function getIsSetup1(): ?bool
    {
        return $this->isSetup1;
    }

    public function setIsSetup1(bool $isSetup1): self
    {
        $this->isSetup1 = $isSetup1;

        return $this;
    }

    public function getIsSetup2(): ?bool
    {
        return $this->isSetup2;
    }

    public function setIsSetup2(bool $isSetup2): self
    {
        $this->isSetup2 = $isSetup2;

        return $this;
    }

    public function getIsSetup3(): ?bool
    {
        return $this->isSetup3;
    }

    public function setIsSetup3(bool $isSetup3): self
    {
        $this->isSetup3 = $isSetup3;

        return $this;
    }

    public function getIsSetup4(): ?bool
    {
        return $this->isSetup4;
    }

    public function setIsSetup4(bool $isSetup4): self
    {
        $this->isSetup4 = $isSetup4;

        return $this;
    }

    public function getIsSetup5(): ?bool
    {
        return $this->isSetup5;
    }

    public function setIsSetup5(bool $isSetup5): self
    {
        $this->isSetup5 = $isSetup5;

        return $this;
    }

    public function getIsSetup6(): ?bool
    {
      return $this->isSetup6;
    }

    public function setIsSetup6(bool $isSetup6): self
    {
      $this->isSetup6 = $isSetup6;

      return $this;
    }

    public function getIsSetupComplete(): ?bool
    {
        return $this->isSetupComplete;
    }

    public function setIsSetupComplete(bool $isSetupComplete): self
    {
        $this->isSetupComplete = $isSetupComplete;

        return $this;
    }

    public function getIsSetup7(): ?bool
    {
        return $this->isSetup7;
    }

    public function setIsSetup7(bool $isSetup7): self
    {
        $this->isSetup7 = $isSetup7;

        return $this;
    }

    public function getIsSetup8(): ?bool
    {
        return $this->isSetup8;
    }

    public function setIsSetup8(bool $isSetup8): self
    {
        $this->isSetup8 = $isSetup8;

        return $this;
    }

}
