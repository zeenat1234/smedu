<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolServiceRepository")
 */
class SchoolService
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolYear", inversedBy="schoolservices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolyear;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolUnit", inversedBy="schoolservices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolunit;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $servicename;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $servicedescription;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $serviceprice;

    public function getId()
    {
        return $this->id;
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

    public function getSchoolunit(): ?SchoolUnit
    {
        return $this->schoolunit;
    }

    public function setSchoolunit(?SchoolUnit $schoolunit): self
    {
        $this->schoolunit = $schoolunit;

        return $this;
    }

    public function getServicename(): ?string
    {
        return $this->servicename;
    }

    public function setServicename(string $servicename): self
    {
        $this->servicename = $servicename;

        return $this;
    }

    public function getServicedescription(): ?string
    {
        return $this->servicedescription;
    }

    public function setServicedescription(string $servicedescription): self
    {
        $this->servicedescription = $servicedescription;

        return $this;
    }

    public function getServiceprice()
    {
        return $this->serviceprice;
    }

    public function setServiceprice($serviceprice): self
    {
        $this->serviceprice = $serviceprice;

        return $this;
    }
}
