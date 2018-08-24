<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MonthAccountRepository")
 */
class MonthAccount
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="monthAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentItem", mappedBy="monthAccount", orphanRemoval=true)
     */
    private $paymentItems;

    /**
     * @ORM\Column(type="datetime")
     */
    private $yearMonth;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $totalPrice;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $totalPaid;

    public function __construct()
    {
        $this->paymentItems = new ArrayCollection();
    }

    public function getId()
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
            $paymentItem->setMonthAccount($this);
        }

        return $this;
    }

    public function removePaymentItem(PaymentItem $paymentItem): self
    {
        if ($this->paymentItems->contains($paymentItem)) {
            $this->paymentItems->removeElement($paymentItem);
            // set the owning side to null (unless already changed)
            if ($paymentItem->getMonthAccount() === $this) {
                $paymentItem->setMonthAccount(null);
            }
        }

        return $this;
    }

    public function getYearMonth(): ?\DateTimeInterface
    {
        return $this->yearMonth;
    }

    public function setYearMonth(\DateTimeInterface $yearMonth): self
    {
        $this->yearMonth = $yearMonth;

        return $this;
    }

    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function setTotalPrice($totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getTotalPaid()
    {
        return $this->totalPaid;
    }

    public function setTotalPaid($totalPaid): self
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }
}
