<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentItemRepository")
 */
class PaymentItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MonthAccount", inversedBy="paymentItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $monthAccount;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $itemName;

    /**
     * @ORM\Column(type="smallint")
     */
    private $itemCount;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $itemPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassOptional", inversedBy="paymentItems")
     */
    private $itemOptional;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEdited;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $editNote;

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

    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    public function setItemName(string $itemName): self
    {
        $this->itemName = $itemName;

        return $this;
    }

    public function getItemCount(): ?int
    {
        return $this->itemCount;
    }

    public function setItemCount(int $itemCount): self
    {
        $this->itemCount = $itemCount;

        return $this;
    }

    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    public function setItemPrice($itemPrice): self
    {
        $this->itemPrice = $itemPrice;

        return $this;
    }

    public function getItemOptional(): ?ClassOptional
    {
        return $this->itemOptional;
    }

    public function setItemOptional(?ClassOptional $itemOptional): self
    {
        $this->itemOptional = $itemOptional;

        return $this;
    }

    public function getIsEdited(): ?bool
    {
        return $this->isEdited;
    }

    public function setIsEdited(bool $isEdited): self
    {
        $this->isEdited = $isEdited;

        return $this;
    }

    public function getEditNote(): ?string
    {
        return $this->editNote;
    }

    public function setEditNote(string $editNote): self
    {
        $this->editNote = $editNote;

        return $this;
    }
}
