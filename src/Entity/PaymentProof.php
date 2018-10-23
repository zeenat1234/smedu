<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentProofRepository")
 */
class PaymentProof
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vă rugăm atașați fișierul care conține dovada plății.")
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/png",
     *          "image/jpeg", "image/jpg"},
     *     mimeTypesMessage = "Formatele suportate sunt următoarele: PDF, PNG, JPG, JPEG"
     * )
     */
    private $proof;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment", inversedBy="paymentProofs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $payment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProof(): ?string
    {
        return $this->proof;
    }

    public function setProof(string $proof): self
    {
        $this->proof = $proof;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }
}
