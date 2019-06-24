<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $payMethod;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $payAmount;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AccountInvoice", inversedBy="payments")
     */
    private $payInvoices;

    /**
     * @ORM\Column(type="datetime")
     */
    private $payDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPending;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isConfirmed;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentProof", mappedBy="payment", orphanRemoval=true)
     */
    private $paymentProofs;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $payAdvance;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SmartReceipt", mappedBy="payment", cascade={"persist", "remove"})
     */
    private $smartReceipt;

    public function __construct()
    {
        $this->payInvoices = new ArrayCollection();
        $this->paymentProofs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayMethod(): ?string
    {
        return $this->payMethod;
    }

    public function setPayMethod(string $payMethod): self
    {
        $this->payMethod = $payMethod;

        return $this;
    }

    public function getPayAmount()
    {
        return $this->payAmount;
    }

    public function setPayAmount($payAmount): self
    {
        $this->payAmount = $payAmount;

        return $this;
    }

    /**
     * @return Collection|AccountInvoice[]
     */
    public function getPayInvoices(): Collection
    {
        return $this->payInvoices;
    }

    public function addPayInvoice(AccountInvoice $payInvoice): self
    {
        if (!$this->payInvoices->contains($payInvoice)) {
            $this->payInvoices[] = $payInvoice;
        }

        return $this;
    }

    public function removePayInvoice(AccountInvoice $payInvoice): self
    {
        if ($this->payInvoices->contains($payInvoice)) {
            $this->payInvoices->removeElement($payInvoice);
        }

        return $this;
    }

    public function getPayDate(): ?\DateTimeInterface
    {
        return $this->payDate;
    }

    public function setPayDate(\DateTimeInterface $payDate): self
    {
        $this->payDate = $payDate;

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

    /**
     * @return Collection|PaymentProof[]
     */
    public function getPaymentProofs(): Collection
    {
        return $this->paymentProofs;
    }

    public function addPaymentProof(PaymentProof $paymentProof): self
    {
        if (!$this->paymentProofs->contains($paymentProof)) {
            $this->paymentProofs[] = $paymentProof;
            $paymentProof->setPayment($this);
        }

        return $this;
    }

    public function removePaymentProof(PaymentProof $paymentProof): self
    {
        if ($this->paymentProofs->contains($paymentProof)) {
            $this->paymentProofs->removeElement($paymentProof);
            // set the owning side to null (unless already changed)
            if ($paymentProof->getPayment() === $this) {
                $paymentProof->setPayment(null);
            }
        }

        return $this;
    }

    public function getPayAdvance()
    {
        return $this->payAdvance;
    }

    public function setPayAdvance($payAdvance): self
    {
        $this->payAdvance = $payAdvance;

        return $this;
    }

    // function used for display purposes only
    public function getNormalized(): ?string
    {
        switch ($this->payMethod) {
          case 'single':
            return '1x Factură (integral)';
          case 'partial':
            return '1x Factură (parțial)';
          case 'multiple':
            return 'Facturi multiple (integral)';
          case 'multiple_partial':
            return 'Facturi multiple (parțial)';
        }
    }

    public function getSmartReceipt(): ?SmartReceipt
    {
        return $this->smartReceipt;
    }

    public function setSmartReceipt(SmartReceipt $smartReceipt): self
    {
        $this->smartReceipt = $smartReceipt;

        // set the owning side of the relation if necessary
        if ($this !== $smartReceipt->getPayment()) {
            $smartReceipt->setPayment($this);
        }

        return $this;
    }

}
