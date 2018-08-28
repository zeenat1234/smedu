<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountInvoiceRepository")
 */
class AccountInvoice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MonthAccount", inversedBy="accountInvoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $monthAccount;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $invoiceName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentItem", mappedBy="accountInvoice")
     */
    private $paymentItems;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sentCount = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $invoiceTotal;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPaid = false;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $invoicePaid = 0.0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payProof;

    public function __construct()
    {
        $this->paymentItems = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMonthAccount(): ?MonthAccount
    {
        return $this->monthAccount;
    }

    public function setMonthAccount(?MonthAccount $monthAccount): self
    {
        $this->monthAccount = $monthAccount;

        return $this;
    }

    public function getInvoiceName(): ?string
    {
        return $this->invoiceName;
    }

    public function setInvoiceName(string $invoiceName): self
    {
        $this->invoiceName = $invoiceName;

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
            $paymentItem->setAccountInvoice($this);
        }

        return $this;
    }

    public function removePaymentItem(PaymentItem $paymentItem): self
    {
        if ($this->paymentItems->contains($paymentItem)) {
            $this->paymentItems->removeElement($paymentItem);
            // set the owning side to null (unless already changed)
            if ($paymentItem->getAccountInvoice() === $this) {
                $paymentItem->setAccountInvoice(null);
            }
        }

        return $this;
    }

    public function getSentCount(): ?int
    {
        return $this->sentCount;
    }

    public function setSentCount(int $sentCount): self
    {
        $this->sentCount = $sentCount;

        return $this;
    }

    public function getInvoiceTotal()
    {
        return $this->invoiceTotal;
    }

    public function setInvoiceTotal($invoiceTotal): self
    {
        $this->invoiceTotal = $invoiceTotal;

        return $this;
    }

    public function getIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getInvoicePaid()
    {
        return $this->invoicePaid;
    }

    public function setInvoicePaid($invoicePaid): self
    {
        $this->invoicePaid = $invoicePaid;

        return $this;
    }

    public function getPayProof(): ?string
    {
        return $this->payProof;
    }

    public function setPayProof(?string $payProof): self
    {
        $this->payProof = $payProof;

        return $this;
    }
}
