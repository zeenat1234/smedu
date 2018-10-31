<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Enrollment", mappedBy="idUnit")
     */
    private $enrollments;

    /**
     * @ORM\Column(type="integer")
     */
    private $availableSpots;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClassGroup", mappedBy="schoolUnit")
     */
    private $classGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClassOptional", mappedBy="schoolUnit")
     */
    private $classOptionals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClassModule", mappedBy="schoolUnit")
     */
    private $classModules;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Student", mappedBy="schoolUnit")
     */
    private $students;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message = "Acest câmp (Serie Factură) nu poate fi gol")
     * @Assert\Length(
     *     min=1, minMessage = "Seria pentru factură trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=10, maxMessage = "Seria pentru factură NU poate să conțină mai mult de '{{ limit }}' caractere"
     * )
     * @Assert\Type(
     *     type="alnum",
     *     message="Seria {{ value }} nu este validă. Aceasta poate să conțină doar litere și cifre!"
     * )
     */
    private $firstInvoiceSerial;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message = "Acest câmp (Număr Factură) nu poate fi gol")
     * @Assert\Length(
     *     min=1, minMessage = "Numărul facturii trebuie să conțină cel puțin '{{ limit }}' cifre",
     *     max=9, maxMessage = "Numărul facturii NU poate să conțină mai mult de '{{ limit }}' cifre"
     * )
     */
    private $firstInvoiceNumber;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message = "Acest câmp (Serie Chitanță) nu poate fi gol")
     * @Assert\Length(
     *     min=1, minMessage = "Seria pentru chitanță trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=10, maxMessage = "Seria pentru chitanță NU poate să conțină mai mult de '{{ limit }}' caractere"
     * )
     * @Assert\Type(
     *     type="alnum",
     *     message="Seria {{ value }} nu este validă. Aceasta poate să conțină doar litere și cifre!"
     * )
     */
    private $firstReceiptSerial;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message = "Acest câmp (Număr Chitanță) nu poate fi gol")
     * @Assert\Length(
     *     min=1, minMessage = "Numărul chitanței trebuie să conțină cel puțin '{{ limit }}' cifre",
     *     max=9, maxMessage = "Numărul chitanței NU poate să conțină mai mult de '{{ limit }}' cifre"
     * )
     */
    private $firstReceiptNumber;

    public function __construct()
    {
        $this->schoolservices = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
        $this->classGroups = new ArrayCollection();
        $this->classOptionals = new ArrayCollection();
        $this->classModules = new ArrayCollection();
        $this->students = new ArrayCollection();
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
            $enrollment->setIdUnit($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): self
    {
        if ($this->enrollments->contains($enrollment)) {
            $this->enrollments->removeElement($enrollment);
            // set the owning side to null (unless already changed)
            if ($enrollment->getIdUnit() === $this) {
                $enrollment->setIdUnit(null);
            }
        }

        return $this;
    }

    public function getAvailableSpots(): ?int
    {
        return $this->availableSpots;
    }

    public function setAvailableSpots(int $availableSpots): self
    {
        $this->availableSpots = $availableSpots;

        return $this;
    }

    /**
     * @return Collection|ClassGroup[]
     */
    public function getClassGroups(): Collection
    {
        return $this->classGroups;
    }

    public function addClassGroup(ClassGroup $classGroup): self
    {
        if (!$this->classGroups->contains($classGroup)) {
            $this->classGroups[] = $classGroup;
            $classGroup->setSchoolUnit($this);
        }

        return $this;
    }

    public function removeClassGroup(ClassGroup $classGroup): self
    {
        if ($this->classGroups->contains($classGroup)) {
            $this->classGroups->removeElement($classGroup);
            // set the owning side to null (unless already changed)
            if ($classGroup->getSchoolUnit() === $this) {
                $classGroup->setSchoolUnit(null);
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
            $classOptional->setSchoolUnit($this);
        }

        return $this;
    }

    public function removeClassOptional(ClassOptional $classOptional): self
    {
        if ($this->classOptionals->contains($classOptional)) {
            $this->classOptionals->removeElement($classOptional);
            // set the owning side to null (unless already changed)
            if ($classOptional->getSchoolUnit() === $this) {
                $classOptional->setSchoolUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ClassModule[]
     */
    public function getClassModules(): Collection
    {
        return $this->classModules;
    }

    public function addClassModule(ClassModule $classModule): self
    {
        if (!$this->classModules->contains($classModule)) {
            $this->classModules[] = $classModule;
            $classModule->setSchoolUnit($this);
        }

        return $this;
    }

    public function removeClassModule(ClassModule $classModule): self
    {
        if ($this->classModules->contains($classModule)) {
            $this->classModules->removeElement($classModule);
            // set the owning side to null (unless already changed)
            if ($classModule->getSchoolUnit() === $this) {
                $classModule->setSchoolUnit(null);
            }
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
            $student->setSchoolUnit($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
            // set the owning side to null (unless already changed)
            if ($student->getSchoolUnit() === $this) {
                $student->setSchoolUnit(null);
            }
        }

        return $this;
    }

    public function getFirstInvoiceSerial(): ?string
    {
        return $this->firstInvoiceSerial;
    }

    public function setFirstInvoiceSerial(string $firstInvoiceSerial): self
    {
        $this->firstInvoiceSerial = $firstInvoiceSerial;

        return $this;
    }

    public function getFirstInvoiceNumber(): ?int
    {
        return $this->firstInvoiceNumber;
    }

    public function setFirstInvoiceNumber(int $firstInvoiceNumber): self
    {
        $this->firstInvoiceNumber = $firstInvoiceNumber;

        return $this;
    }

    public function getFirstReceiptSerial(): ?string
    {
        return $this->firstReceiptSerial;
    }

    public function setFirstReceiptSerial(string $firstReceiptSerial): self
    {
        $this->firstReceiptSerial = $firstReceiptSerial;

        return $this;
    }

    public function getFirstReceiptNumber(): ?int
    {
        return $this->firstReceiptNumber;
    }

    public function setFirstReceiptNumber(int $firstReceiptNumber): self
    {
        $this->firstReceiptNumber = $firstReceiptNumber;

        return $this;
    }

}
