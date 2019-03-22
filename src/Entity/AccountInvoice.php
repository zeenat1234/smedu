<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountInvoiceRepository")
 * @UniqueEntity(
 *     fields={"invoiceSerial", "invoiceNumber"},
 *     message="Combinația serie/număr pentru această factură există deja!",
 *     groups = {"invoiceDetails"}
 * )
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
     * @Assert\NotBlank(message="Vă rugăm atașați fișierul care conține dovada plății.")
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/png",
     *          "image/jpeg", "image/jpg"},
     *     mimeTypesMessage = "Formatele suportate sunt următoarele: PDF, PNG, JPG, JPEG",
     *     groups = {"invoiceUpload"}
     * )
     */
    private $payProof;

    /**
     * @ORM\Column(type="datetime")
     */
    private $invoiceDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $invoiceSentDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $invoicePaidDate;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message = "Acest câmp (Serie Factură) nu poate fi gol", groups = {"invoiceDetails"})
     * @Assert\Length(
     *     min=1, minMessage = "Seria pentru factură trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=10, maxMessage = "Seria pentru factură NU poate să conțină mai mult de '{{ limit }}' caractere",
     *     groups = {"invoiceDetails"}
     * )
     * @Assert\Type(
     *     type="alnum",
     *     message="Seria {{ value }} nu este validă. Aceasta poate să conțină doar litere și cifre!",
     *     groups = {"invoiceDetails"}
     * )
     */
    private $invoiceSerial;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message = "Acest câmp (Număr Factură) nu poate fi gol", groups = {"invoiceDetails"})
     * @Assert\Length(
     *     min=1, minMessage = "Numărul facturii trebuie să conțină cel puțin '{{ limit }}' cifre",
     *     max=9, maxMessage = "Numărul facturii NU poate să conțină mai mult de '{{ limit }}' cifre",
     *     groups = {"invoiceDetails"}
     * )
     */
    private $invoiceNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isProforma = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLocked = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\AccountInvoice", mappedBy="trueInvoice", cascade={"persist"})
     */
    private $trueAccountInvoice; //child reference to the parent PROFORMA INVOICE

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\AccountInvoice", inversedBy="trueAccountInvoice", cascade={"persist", "remove"})
     */
    private $trueInvoice; //gets the value of the FISCAL INVOICE -- used on proforma when creating actual invoice

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\AccountReceipt", mappedBy="accountInvoice", cascade={"persist", "remove"})
     */
    private $accountReceipt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $payeeIsCompany = false;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $payeeName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payeeAddress;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $payeeIdent;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $payeeCompanyReg;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $payeeCompanyFiscal;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Payment", mappedBy="payInvoices")
     */
    private $payments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="createdInvoices")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="smallint")
     */
    private $penaltyDays = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $penaltySum = 0.0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $penaltyInvoiced = false;

    /**
     * @ORM\Column(type="smallint")
     */
    private $partialPenaltyDays = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $partialPenaltySum = 0.0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $partialPenaltyInvoiced = false;

    public function __construct()
    {
        $this->paymentItems = new ArrayCollection();
        $this->payments = new ArrayCollection();
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

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): self
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getInvoiceSentDate(): ?\DateTimeInterface
    {
        return $this->invoiceSentDate;
    }

    public function setInvoiceSentDate(?\DateTimeInterface $invoiceSentDate): self
    {
        $this->invoiceSentDate = $invoiceSentDate;

        return $this;
    }

    public function getInvoicePaidDate(): ?\DateTimeInterface
    {
        return $this->invoicePaidDate;
    }

    public function setInvoicePaidDate(?\DateTimeInterface $invoicePaidDate): self
    {
        $this->invoicePaidDate = $invoicePaidDate;

        return $this;
    }

    public function getInvoiceSerial(): ?string
    {
        return $this->invoiceSerial;
    }

    public function setInvoiceSerial(string $invoiceSerial): self
    {
        $this->invoiceSerial = $invoiceSerial;

        return $this;
    }

    public function getInvoiceNumber(): ?int
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(int $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getIsProforma(): ?bool
    {
        return $this->isProforma;
    }

    public function setIsProforma(bool $isProforma): self
    {
        $this->isProforma = $isProforma;

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

    public function getTrueAccountInvoice(): ?self
    {
        return $this->trueAccountInvoice;
    }

    public function setTrueAccountInvoice(?self $trueAccountInvoice): self
    {
        $this->trueAccountInvoice = $trueAccountInvoice;

        // set (or unset) the owning side of the relation if necessary
        $newTrueInvoice = $trueAccountInvoice === null ? null : $this;
        if ($newTrueInvoice !== $trueAccountInvoice->getTrueInvoice()) {
            $trueAccountInvoice->setTrueInvoice($newTrueInvoice);
        }

        return $this;
    }

    public function getTrueInvoice(): ?self
    {
        return $this->trueInvoice;
    }

    public function setTrueInvoice(?self $trueInvoice): self
    {
        $this->trueInvoice = $trueInvoice;

        // set (or unset) the owning side of the relation if necessary - DO MANUALLY!!!
        // $newTrueAccountInvoice = $trueInvoice === null ? null : $this;
        // if ($newTrueAccountInvoice !== $trueInvoice->getTrueAccountInvoice()) {
        //     $trueInvoice->setTrueAccountInvoice($newTrueAccountInvoice);
        // }

        return $this;
    }

    public function getAccountReceipt(): ?AccountReceipt
    {
        return $this->accountReceipt;
    }

    public function setAccountReceipt(AccountReceipt $accountReceipt): self
    {
        $this->accountReceipt = $accountReceipt;

        // set the owning side of the relation if necessary
        if ($this !== $accountReceipt->getAccountInvoice()) {
            $accountReceipt->setAccountInvoice($this);
        }

        return $this;
    }

    public function getPayeeIsCompany(): ?bool
    {
        return $this->payeeIsCompany;
    }

    public function setPayeeIsCompany(bool $payeeIsCompany): self
    {
        $this->payeeIsCompany = $payeeIsCompany;

        return $this;
    }

    public function getPayeeName(): ?string
    {
        return $this->payeeName;
    }

    public function setPayeeName(?string $payeeName): self
    {
        $this->payeeName = $payeeName;

        return $this;
    }

    public function getPayeeAddress(): ?string
    {
        return $this->payeeAddress;
    }

    public function setPayeeAddress(?string $payeeAddress): self
    {
        $this->payeeAddress = $payeeAddress;

        return $this;
    }

    public function getPayeeIdent(): ?string
    {
        return $this->payeeIdent;
    }

    public function setPayeeIdent(?string $payeeIdent): self
    {
        $this->payeeIdent = $payeeIdent;

        return $this;
    }

    public function getPayeeCompanyReg(): ?string
    {
        return $this->payeeCompanyReg;
    }

    public function setPayeeCompanyReg(?string $payeeCompanyReg): self
    {
        $this->payeeCompanyReg = $payeeCompanyReg;

        return $this;
    }

    public function getPayeeCompanyFiscal(): ?string
    {
        return $this->payeeCompanyFiscal;
    }

    public function setPayeeCompanyFiscal(?string $payeeCompanyFiscal): self
    {
        $this->payeeCompanyFiscal = $payeeCompanyFiscal;

        return $this;
    }

    /**
     * @return Collection|Payment[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->addPayInvoice($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            $payment->removePayInvoice($this);
        }

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

    public function getPenaltyDays(): ?int
    {
        return $this->penaltyDays;
    }

    public function setPenaltyDays(int $penaltyDays): self
    {
        $this->penaltyDays = $penaltyDays;

        return $this;
    }

    public function getPenaltySum(): ?int
    {
        return $this->penaltySum;
    }

    public function setPenaltySum(int $penaltySum): self
    {
        $this->penaltySum = $penaltySum;

        return $this;
    }

    public function getPenaltyInvoiced(): ?bool
    {
        return $this->penaltyInvoiced;
    }

    public function setPenaltyInvoiced(bool $penaltyInvoiced): self
    {
        $this->penaltyInvoiced = $penaltyInvoiced;

        return $this;
    }

    public function getPartialPenaltyDays(): ?int
    {
        return $this->partialPenaltyDays;
    }

    public function setPartialPenaltyDays(int $partialPenaltyDays): self
    {
        $this->partialPenaltyDays = $partialPenaltyDays;

        return $this;
    }

    public function getPartialPenaltySum()
    {
        return $this->partialPenaltySum;
    }

    public function setPartialPenaltySum($partialPenaltySum): self
    {
        $this->partialPenaltySum = $partialPenaltySum;

        return $this;
    }

    public function getPartialPenaltyInvoiced(): ?bool
    {
        return $this->partialPenaltyInvoiced;
    }

    public function setPartialPenaltyInvoiced(bool $partialPenaltyInvoiced): self
    {
        $this->partialPenaltyInvoiced = $partialPenaltyInvoiced;

        return $this;
    }
}
