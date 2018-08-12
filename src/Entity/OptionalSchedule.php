<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OptionalScheduleRepository")
 */
class OptionalSchedule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassOptional", inversedBy="optionalSchedules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classOptional;

    /**
     * @ORM\Column(type="datetime")
     */
    private $scheduledDateTime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionalsAttendance", mappedBy="optionalSchedule", orphanRemoval=true)
     */
    private $optionalsAttendances;

    public function __construct()
    {
        $this->optionalsAttendances = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getClassOptional(): ?ClassOptional
    {
        return $this->classOptional;
    }

    public function setClassOptional(?ClassOptional $classOptional): self
    {
        $this->classOptional = $classOptional;

        return $this;
    }

    public function getScheduledDateTime(): ?\DateTimeInterface
    {
        return $this->scheduledDateTime;
    }

    public function setScheduledDateTime(\DateTimeInterface $scheduledDateTime): self
    {
        $this->scheduledDateTime = $scheduledDateTime;

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
            $optionalsAttendance->setOptionalSchedule($this);
        }

        return $this;
    }

    public function removeOptionalsAttendance(OptionalsAttendance $optionalsAttendance): self
    {
        if ($this->optionalsAttendances->contains($optionalsAttendance)) {
            $this->optionalsAttendances->removeElement($optionalsAttendance);
            // set the owning side to null (unless already changed)
            if ($optionalsAttendance->getOptionalSchedule() === $this) {
                $optionalsAttendance->setOptionalSchedule(null);
            }
        }

        return $this;
    }
}
