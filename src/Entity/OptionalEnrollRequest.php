<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OptionalEnrollRequestRepository")
 */
class OptionalEnrollRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="optionalEnrollRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $rUser;

    /**
     * @ORM\Column(type="datetime")
     */
    private $rDateTime;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ClassOptional", inversedBy="optionalEnrollRequests")
     */
    private $rOptionals;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="optionalEnrollRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $rStudent;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPending;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isConfirmed = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $rType;

    public function __construct()
    {
        $this->rOptionals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRUser(): ?User
    {
        return $this->rUser;
    }

    public function setRUser(?User $rUser): self
    {
        $this->rUser = $rUser;

        return $this;
    }

    public function getRDateTime(): ?\DateTimeInterface
    {
        return $this->rDateTime;
    }

    public function setRDateTime(\DateTimeInterface $rDateTime): self
    {
        $this->rDateTime = $rDateTime;

        return $this;
    }

    /**
     * @return Collection|ClassOptional[]
     */
    public function getROptionals(): Collection
    {
        return $this->rOptionals;
    }

    public function addROptional(ClassOptional $rOptional): self
    {
        if (!$this->rOptionals->contains($rOptional)) {
            $this->rOptionals[] = $rOptional;
        }

        return $this;
    }

    public function removeROptional(ClassOptional $rOptional): self
    {
        if ($this->rOptionals->contains($rOptional)) {
            $this->rOptionals->removeElement($rOptional);
        }

        return $this;
    }

    public function getRStudent(): ?Student
    {
        return $this->rStudent;
    }

    public function setRStudent(?Student $rStudent): self
    {
        $this->rStudent = $rStudent;

        return $this;
    }

    public function getIsPending(): ?bool
    {
        return $this->isPending;
    }

    public function setIsPending(bool $isPending): self
    {
        $this->isPending = $isPending;

        return $this;
    }

    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    public function getRType(): ?bool
    {
        return $this->rType;
    }

    public function setRType(bool $rType): self
    {
        $this->rType = $rType;

        return $this;
    }
}
