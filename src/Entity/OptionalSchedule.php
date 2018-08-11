<?php

namespace App\Entity;

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
}
