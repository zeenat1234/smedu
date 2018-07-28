<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

//Validation Classes
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EnrollmentRepository")
 * @UniqueEntity(
 *     fields = {"idChild", "idUnit"},
 *     message = "The student is already enrolled in this unit"
 * )
 */
class Enrollment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="enrollmentsParent")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idParent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="enrollmentsChild")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idChild;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolUnit", inversedBy="enrollments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idUnit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolService", inversedBy="enrollments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idService;

    /**
     * @ORM\Column(type="datetime")
     */
    private $enrollDate;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $notes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolYear", inversedBy="enrollments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolYear;

    public function getId()
    {
        return $this->id;
    }

    public function getIdParent(): ?User
    {
        return $this->idParent;
    }

    public function setIdParent(?User $idParent): self
    {
        $this->idParent = $idParent;

        return $this;
    }

    public function getIdChild(): ?User
    {
        return $this->idChild;
    }

    public function setIdChild(?User $idChild): self
    {
        $this->idChild = $idChild;

        return $this;
    }

    public function getIdUnit(): ?SchoolUnit
    {
        return $this->idUnit;
    }

    public function setIdUnit(?SchoolUnit $idUnit): self
    {
        $this->idUnit = $idUnit;

        return $this;
    }

    public function getIdService(): ?SchoolService
    {
        return $this->idService;
    }

    public function setIdService(?SchoolService $idService): self
    {
        $this->idService = $idService;

        return $this;
    }

    public function getEnrollDate(): ?\DateTimeInterface
    {
        return $this->enrollDate;
    }

    public function setEnrollDate(\DateTimeInterface $enrollDate): self
    {
        $this->enrollDate = $enrollDate;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSchoolYear(): ?SchoolYear
    {
        return $this->schoolYear;
    }

    public function setSchoolYear(?SchoolYear $schoolYear): self
    {
        $this->schoolYear = $schoolYear;

        return $this;
    }
}
