<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransportTripRepository")
 */
class TransportTrip
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="transportTrips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $distance1 = 0;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $distance2 = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pricePerKm;

    /**
     * @ORM\Column(type="integer")
     */
    private $tripType = 0;
    // 'Absent' => 0,
    // 'Dus' => 1,
    // 'Întors' => 2,
    // 'Dus/Întors' => 3,

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDistance1()
    {
        return $this->distance1;
    }

    public function setDistance1($distance1): self
    {
        $this->distance1 = $distance1;

        return $this;
    }

    public function getDistance2()
    {
        return $this->distance2;
    }

    public function setDistance2($distance2): self
    {
        $this->distance2 = $distance2;

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

    public function getPricePerKm(): ?bool
    {
        return $this->pricePerKm;
    }

    public function setPricePerKm(bool $pricePerKm): self
    {
        $this->pricePerKm = $pricePerKm;

        return $this;
    }

    public function getTripType(): ?int
    {
        return $this->tripType;
    }

    public function setTripType(int $tripType): self
    {
        $this->tripType = $tripType;

        return $this;
    }
}
