<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionalsAttendance", mappedBy="classOptional", orphanRemoval=true)
     */
    private $optionalsAttendances;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="classOptionals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $professor;

    /**
     * @ORM\Column(type="boolean")
     */
    private $useAttend = true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentItem", mappedBy="itemOptional")
     */
    private $paymentItems;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\OptionalEnrollRequest", mappedBy="rOptionals")
     */
    private $optionalEnrollRequests;

    public function __construct()
    {
        $this->inServices = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->optionalSchedules = new ArrayCollection();
        $this->optionalsAttendances = new ArrayCollection();
        $this->paymentItems = new ArrayCollection();
        $this->optionalEnrollRequests = new ArrayCollection();
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

    /**
     * @return Collection|OptionalSchedule[]
     */
    public function getDescOptionalSchedules(): Collection
    {
        $criteria = Criteria::create()
          ->orderBy(array('scheduledDateTime' => Criteria::DESC))
          ->setFirstResult(0)
        ;

        return $this->optionalSchedules->matching($criteria);
    }

    /**
     * @return Collection|OptionalSchedule[]
     */
    public function getAscOptionalSchedules(): Collection
    {
        $criteria = Criteria::create()
          ->orderBy(array('scheduledDateTime' => Criteria::ASC))
          ->setFirstResult(0)
        ;

        return $this->optionalSchedules->matching($criteria);
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

    /**
     * @return Collection|OptionalsAttendance[]
     */
    public function getOptionalsAttendances(): Collection
    {
        return $this->optionalsAttendances;
    }

    public function addOptionalsAttendance(OptionalsAttendance $optionalsAttendance): self
    {
        if (!$this->optionalsAttendances->contains($optionalsAttendance)) {
            $this->optionalsAttendances[] = $optionalsAttendance;
            $optionalsAttendance->setClassOptional($this);
        }

        return $this;
    }

    public function removeOptionalsAttendance(OptionalsAttendance $optionalsAttendance): self
    {
        if ($this->optionalsAttendances->contains($optionalsAttendance)) {
            $this->optionalsAttendances->removeElement($optionalsAttendance);
            // set the owning side to null (unless already changed)
            if ($optionalsAttendance->getClassOptional() === $this) {
                $optionalsAttendance->setClassOptional(null);
            }
        }

        return $this;
    }

    public function getProfessor(): ?User
    {
        return $this->professor;
    }

    public function setProfessor(?User $professor): self
    {
        $this->professor = $professor;

        return $this;
    }

    public function isSyncd()
    {
        $criteria = Criteria::create()
          ->where(Criteria::expr()->gt("scheduledDateTime", (new \DateTime('now'))))
          ->setFirstResult(0)
          //->setMaxResults(1)
        ;
        $all_schedules = $this->optionalSchedules->getValues();
        $sync_schedules = $this->optionalSchedules->matching($criteria)->getValues();

        $sync_students = $this->students->getValues();

        $all_attendances = $this->optionalsAttendances;
        $sync_attendances = array();
        //verify against attendances from now and onwards
        foreach ($all_attendances as $attendance) {
            if ($attendance->getOptionalSchedule()->getScheduledDateTime() > (new \DateTime('now'))) {
               $sync_attendances[]=$attendance;
            }
        }

        //if there are no schedules or students defined, consider the optional not syncd
        if ((count($sync_schedules) == 0 && count($all_schedules) == 0) || (count($sync_students) == 0)) {
          return false;
        }

        if (count($sync_schedules) == 0 && count($all_schedules) > 0 && count($all_attendances) > 0) {
          return true;
        }

        //create attendance list and schedule list to verify students and schedules
        $attendees = array();
        $schedules = array();

        foreach ($sync_attendances as $attendance) {
          if (!in_array($attendance->getStudent(), $attendees)) {
            $attendees[] = $attendance->getStudent();
          }
          if (!in_array($attendance->getOptionalSchedule(), $schedules)) {
            $schedules[] = $attendance->getOptionalSchedule();
          }
        }

        //check if number of schedules and students match the attendance register
        if (count($sync_schedules)*count($sync_students) === count($sync_attendances)) {
          //check if attendance list students matches the enrolled students && attendance list schedules matches existing schedules
          if ((count(array_uintersect($attendees, $sync_students, function($attendees, $sync_students) {
              return strcmp(spl_object_hash($attendees), spl_object_hash($sync_students));
          })) == count($sync_students)) &&
              (count(array_uintersect($schedules, $sync_schedules, function($schedules, $sync_schedules) {
                  return strcmp(spl_object_hash($schedules), spl_object_hash($sync_schedules));
              })) == count($sync_schedules)))
              //Comment from Stack Overflow:
              //Nice solution. For contemporary readers: in php7, the strcmp() can be replaced
              //with the <=> operator, like this: return spl_object_hash($a) <=> spl_object_hash($b);
          {
            return true;
          }
        } else {
          return false;
        }
    }

    //The following should only be used after verifying if the optional is synchronized with the function isSyncd()
    public function isModified()
    {

        $attendances = $this->getOptionalsAttendances();
        $attendCount = count($attendances);

        if ($attendCount == 0) {
          return false;
        } elseif ($attendCount >= 1) {
          return true;
        }
    }

    public function getUseAttend(): ?bool
    {
        return $this->useAttend;
    }

    public function setUseAttend(bool $useAttend): self
    {
        $this->useAttend = $useAttend;

        return $this;
    }

    /**
     * @return Collection|PaymentItem[]
     */
    public function getPaymentItems(): Collection
    {
        return $this->paymentItems;
    }

    public function addPaymentItem(PaymentItem $paymentItem): self
    {
        if (!$this->paymentItems->contains($paymentItem)) {
            $this->paymentItems[] = $paymentItem;
            $paymentItem->setItemOptional($this);
        }

        return $this;
    }

    public function removePaymentItem(PaymentItem $paymentItem): self
    {
        if ($this->paymentItems->contains($paymentItem)) {
            $this->paymentItems->removeElement($paymentItem);
            // set the owning side to null (unless already changed)
            if ($paymentItem->getItemOptional() === $this) {
                $paymentItem->setItemOptional(null);
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
            $optionalEnrollRequest->addROptional($this);
        }

        return $this;
    }

    public function removeOptionalEnrollRequest(OptionalEnrollRequest $optionalEnrollRequest): self
    {
        if ($this->optionalEnrollRequests->contains($optionalEnrollRequest)) {
            $this->optionalEnrollRequests->removeElement($optionalEnrollRequest);
            $optionalEnrollRequest->removeROptional($this);
        }

        return $this;
    }
}
