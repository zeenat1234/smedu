<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransportRouteRepository")
 */
class TransportRoute
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Student", inversedBy="transportRoute")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $distance = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pricePerKm = true;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $price = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getDistance()
    {
        return $this->distance;
    }

    public function setDistance($distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getPricePerKm(): ?bool
    {
        return $this->pricePerKm;
    }

    public function setPricePerKm(bool $pricePerKm): self
    {
        $this->pricePerKm = $pricePerKm;

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
}
