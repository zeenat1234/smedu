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
    private $accYearMonth;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $totalPrice = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $totalPaid = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccountInvoice", mappedBy="monthAccount", orphanRemoval=true)
     */
    private $accountInvoices;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $advanceBalance = 0;

    public function __construct()
    {
        $this->paymentItems = new ArrayCollection();
        $this->accountInvoices = new ArrayCollection();
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

    public function getAccYearMonth(): ?\DateTimeInterface
    {
        return $this->accYearMonth;
    }

    public function setAccYearMonth(\DateTimeInterface $accYearMonth): self
    {
        $this->accYearMonth = $accYearMonth;

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

    public function addToTotalPrice($addedPrice): self
    {
        $this->totalPrice += $addedPrice;

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

    /**
     * @return Collection|AccountInvoice[]
     */
    public function getAccountInvoices(): Collection
    {
        return $this->accountInvoices;
    }

    public function addAccountInvoice(AccountInvoice $accountInvoice): self
    {
        if (!$this->accountInvoices->contains($accountInvoice)) {
            $this->accountInvoices[] = $accountInvoice;
            $accountInvoice->setMonthAccount($this);
        }

        return $this;
    }

    public function removeAccountInvoice(AccountInvoice $accountInvoice): self
    {
        if ($this->accountInvoices->contains($accountInvoice)) {
            $this->accountInvoices->removeElement($accountInvoice);
            // set the owning side to null (unless already changed)
            if ($accountInvoice->getMonthAccount() === $this) {
                $accountInvoice->setMonthAccount(null);
            }
        }

        return $this;
    }

    public function getAdvanceBalance(): ?int
    {
        return $this->advanceBalance;
    }

    public function setAdvanceBalance(int $advanceBalance): self
    {
        $this->advanceBalance = $advanceBalance;

        return $this;
    }
}
