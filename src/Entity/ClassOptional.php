<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    public function __construct()
    {
        $this->inServices = new ArrayCollection();
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
}
