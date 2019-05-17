<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmartReceiptRepository")
 * @UniqueEntity(
 *     fields={"receiptSerial", "receiptNumber"},
 *     message="Combinația serie/număr pentru această chitanță există deja!",
 *     groups = {"receiptDetails"}
 * )
 */
class SmartReceipt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\Payment", inversedBy="smartReceipt")
    * @ORM\JoinColumn(nullable=true)
    */
    private $payment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $receiptDate;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message = "Acest câmp (Serie Chitanță) nu poate fi gol", groups = {"receiptDetails"})
     * @Assert\Length(
     *     min=1, minMessage = "Seria pentru chitanță trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=10, maxMessage = "Seria pentru chitanță NU poate să conțină mai mult de '{{ limit }}' caractere",
     *     groups = {"receiptDetails"}
     * )
     * @Assert\Type(
     *     type="alnum",
     *     message="Seria {{ value }} nu este validă. Aceasta poate să conțină doar litere și cifre!",
     *     groups = {"receiptDetails"}
     * )
     */
    private $receiptSerial;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message = "Acest câmp (Număr Chitanță) nu poate fi gol", groups = {"receiptDetails"})
     * @Assert\Length(
     *     min=1, minMessage = "Numărul chitanței trebuie să conțină cel puțin '{{ limit }}' cifre",
     *     max=9, maxMessage = "Numărul chitanței NU poate să conțină mai mult de '{{ limit }}' cifre",
     *     groups = {"receiptDetails"}
     * )
     */
    private $receiptNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLocked = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="createdReceipts")
     */
    private $createdBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): ?Payment
    {
      return $this->payment;
    }

    public function setPayment(Payment $payment=NULL): self
    {
      $this->payment = $payment;

      return $this;
    }

    public function getReceiptDate(): ?\DateTimeInterface
    {
        return $this->receiptDate;
    }

    public function setReceiptDate(\DateTimeInterface $receiptDate): self
    {
        $this->receiptDate = $receiptDate;

        return $this;
    }

    public function getReceiptSerial(): ?string
    {
        return $this->receiptSerial;
    }

    public function setReceiptSerial(string $receiptSerial): self
    {
        $this->receiptSerial = $receiptSerial;

        return $this;
    }

    public function getReceiptNumber(): ?int
    {
        return $this->receiptNumber;
    }

    public function setReceiptNumber(int $receiptNumber): self
    {
        $this->receiptNumber = $receiptNumber;

        return $this;
    }

    public function getIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
