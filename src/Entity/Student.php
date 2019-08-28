<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassGroup", inversedBy="students", cascade={"all"})
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
     * @ORM\OneToOne(targetEntity="App\Entity\Enrollment", mappedBy="student", orphanRemoval=true, cascade={"all"})
     */
    private $enrollment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionalsAttendance", mappedBy="student", orphanRemoval=true)
     */
    private $optionalsAttendances;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MonthAccount", mappedBy="student", orphanRemoval=true)
     */
    private $monthAccounts;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TransportRoute", mappedBy="student", cascade={"persist", "remove"})
     */
    private $transportRoute;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TransportTrip", mappedBy="student", orphanRemoval=true)
     */
    private $transportTrips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionalEnrollRequest", mappedBy="rStudent", orphanRemoval=true)
     */
    private $optionalEnrollRequests;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Student", cascade={"persist", "remove"})
     */
    private $importedFrom;

    public function __construct()
    {
        $this->ClassOptionals = new ArrayCollection();
        $this->optionalsAttendances = new ArrayCollection();
        $this->monthAccounts = new ArrayCollection();
        $this->transportTrips = new ArrayCollection();
        $this->optionalEnrollRequests = new ArrayCollection();
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

    /**
     * @return Collection|OptionalsAttendance[]
     */
    public function getOptionalsAttendances(): Collection
    {
        return $this->optionalsAttendances;
    }

    public function getAttendanceBySched(OptionalSchedule $sched): ?OptionalsAttendance
    {
        $criteria = Criteria::create()
          ->where(Criteria::expr()->eq("student", $this))
          ->where(Criteria::expr()->eq("optionalSchedule", $sched))
          ->setFirstResult(0)
          ->setMaxResults(1)
        ;

        return $this->optionalsAttendances->matching($criteria)[0];
    }

    public function addOptionalsAttendance(OptionalsAttendance $optionalsAttendance): self
    {
        if (!$this->optionalsAttendances->contains($optionalsAttendance)) {
            $this->optionalsAttendances[] = $optionalsAttendance;
            $optionalsAttendance->setStudent($this);
        }

        return $this;
    }

    public function removeOptionalsAttendance(OptionalsAttendance $optionalsAttendance): self
    {
        if ($this->optionalsAttendances->contains($optionalsAttendance)) {
            $this->optionalsAttendances->removeElement($optionalsAttendance);
            // set the owning side to null (unless already changed)
            if ($optionalsAttendance->getStudent() === $this) {
                $optionalsAttendance->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MonthAccount[]
     */
    public function getMonthAccounts(): Collection
    {
        return $this->monthAccounts;
    }

    /**
     * @return Collection|MonthAccount[]
     */
    public function getDescMonthAccounts(): Collection
    {
        $criteria = Criteria::create()
          ->where(Criteria::expr()->eq("student", $this))
          ->orderBy(array('accYearMonth' => Criteria::DESC))
          ->setFirstResult(0)
        ;

        return $this->monthAccounts->matching($criteria);
    }

    public function getLatestMonthAccount(): ?MonthAccount
    {
        $criteria = Criteria::create()
          ->where(Criteria::expr()->eq("student", $this))
          ->orderBy(array('accYearMonth' => Criteria::DESC))
          ->setFirstResult(0)
          ->setMaxResults(1)
        ;

        return $this->monthAccounts->matching($criteria)[0];
    }

    public function addMonthAccount(MonthAccount $monthAccount): self
    {
        if (!$this->monthAccounts->contains($monthAccount)) {
            $this->monthAccounts[] = $monthAccount;
            $monthAccount->setStudent($this);
        }

        return $this;
    }

    public function removeMonthAccount(MonthAccount $monthAccount): self
    {
        if ($this->monthAccounts->contains($monthAccount)) {
            $this->monthAccounts->removeElement($monthAccount);
            // set the owning side to null (unless already changed)
            if ($monthAccount->getStudent() === $this) {
                $monthAccount->setStudent(null);
            }
        }

        return $this;
    }

    public function getTransportRoute(): ?TransportRoute
    {
        return $this->transportRoute;
    }

    public function setTransportRoute(TransportRoute $transportRoute): self
    {
        $this->transportRoute = $transportRoute;

        // set the owning side of the relation if necessary
        if ($this !== $transportRoute->getStudent()) {
            $transportRoute->setStudent($this);
        }

        return $this;
    }

    /**
     * @return Collection|TransportTrip[]
     */
    public function getTransportTrips(): Collection
    {
        return $this->transportTrips;
    }

    /**
     * @return Collection|TransportTrip[]
     */
    public function getTransportTripsDesc(): Collection
    {
        $criteria = Criteria::create()
          ->orderBy(array('date' => Criteria::DESC))
          ->setFirstResult(0)
        ;
        return $this->transportTrips->matching($criteria);
    }

    public function getTransportTripByDay($date): ?TransportTrip
    {
        $criteria = Criteria::create()
          ->where(Criteria::expr()->eq("student", $this))
          ->where(Criteria::expr()->eq("date", (new \DateTime($date))))
          ->setFirstResult(0)
          ->setMaxResults(1)
        ;

        return $this->transportTrips->matching($criteria)[0];
    }

    public function addTransportTrip(TransportTrip $transportTrip): self
    {
        if (!$this->transportTrips->contains($transportTrip)) {
            $this->transportTrips[] = $transportTrip;
            $transportTrip->setStudent($this);
        }

        return $this;
    }

    public function removeTransportTrip(TransportTrip $transportTrip): self
    {
        if ($this->transportTrips->contains($transportTrip)) {
            $this->transportTrips->removeElement($transportTrip);
            // set the owning side to null (unless already changed)
            if ($transportTrip->getStudent() === $this) {
                $transportTrip->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OptionalEnrollRequest[]
     */
    public function getOptionalEnrollRequests(): Collection
    {
        return $this->optionalEnrollRequests;
    }

    public function addOptionalEnrollRequest(OptionalEnrollRequest $optionalEnrollRequest): self
    {
        if (!$this->optionalEnrollRequests->contains($optionalEnrollRequest)) {
            $this->optionalEnrollRequests[] = $optionalEnrollRequest;
            $optionalEnrollRequest->setRStudent($this);
        }

        return $this;
    }

    public function removeOptionalEnrollRequest(OptionalEnrollRequest $optionalEnrollRequest): self
    {
        if ($this->optionalEnrollRequests->contains($optionalEnrollRequest)) {
            $this->optionalEnrollRequests->removeElement($optionalEnrollRequest);
            // set the owning side to null (unless already changed)
            if ($optionalEnrollRequest->getRStudent() === $this) {
                $optionalEnrollRequest->setRStudent(null);
            }
        }

        return $this;
    }

    public function getImportedFrom(): ?self
    {
        return $this->importedFrom;
    }

    public function setImportedFrom(?self $importedFrom): self
    {
        $this->importedFrom = $importedFrom;

        return $this;
    }
}
