<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\Enrollment;

//Validation Classes
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Acest e-mail este deja folosit"
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     message="Acest username este deja folosit"
 * )
 * @UniqueEntity(
 *     fields={"firstName", "lastName"},
 *     message="Acest utilizator (nume, prenume) există deja în baza de date",
 *     errorPath="username"
 * )
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     * @Assert\NotBlank(message = "Acest câmp (username) nu poate fi gol")
     * @Assert\Length(
     *     min=4, minMessage = "Numele de utilizator trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=32, maxMessage = "Numele de utilizator NU poate să conțină mai mult de '{{ limit }}' caractere"
     * )
     * @Assert\Regex(
     *     pattern="/\A[a-zA-Z0-9ăîșțâĂÎȘȚÂ]+([-\.][a-zA-Z0-9ăîșțâĂÎȘȚÂ]+)*\z/",
     *     message="Numele de utilizator poate să conțină doar litere și caracterele '.' și '-'"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     * @Assert\NotBlank(message = "Acest câmp (e-mail) nu poate fi gol")
     * @Assert\Email(
     *     message = "Adresa de e-mail '{{ value }}' nu este validă",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\Length(
     *     min=8, minMessage="Parola trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=64, maxMessage="Parola NU poate să conțină mai mult de '{{ limit }}' caractere"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $usertype;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Enrollment", mappedBy="idParent")
     */
    private $enrollmentsParent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Enrollment", mappedBy="idChild")
     */
    private $enrollmentsChild;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClassGroup", mappedBy="professor")
     */
    private $classGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Student", mappedBy="User", orphanRemoval=true)
     */
    private $students;

    /**
     * @ORM\Column(type="string", length=32)
     * @Assert\NotBlank(message = "Câmpul \'prenume\' nu poate fi gol.")
     * @Assert\Length(
     *     min=2, minMessage = "Câmpul 'nume' trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=32, maxMessage = "Câmpul 'nume' can NU poate să conțină mai mult de '{{ limit }}' caractere"
     * )
     * @Assert\Regex(
     *     pattern="/\A[a-zA-ZăîșțâĂÎȘȚÂ]+([\s|-][a-zA-ZăîșțâĂÎȘȚÂ]+)*\z/",
     *     message="Câmpul 'nume' poate să conțină doar litere, spații și caracterele '.' și '-'"
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=32)
     * @Assert\NotBlank(message = "Câmpul 'prenume' nu poate fi gol.")
     * @Assert\Length(
     *     min=2, minMessage = "Câmpul 'prenume' trebuie să conțină cel puțin '{{ limit }}' caractere",
     *     max=32, maxMessage = "Câmpul 'prenume' can NU poate să conțină mai mult de '{{ limit }}' caractere"
     * )
     * @Assert\Regex(
     *     pattern="/\A[a-zA-ZăîșțâĂÎȘȚÂ]+([\s|-][a-zA-ZăîșțâĂÎȘȚÂ]+)*\z/",
     *     message="Câmpul 'prenume' poate să conțină doar litere, spații și caracterele '.' și '-'"
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $phoneNo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClassOptional", mappedBy="professor")
     */
    private $classOptionals;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Guardian", inversedBy="children")
     */
    private $guardian;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Guardian", mappedBy="user", cascade={"persist", "remove"})
     */
    private $guardianacc;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AccountPermission", mappedBy="users", cascade={"persist"})
     */
    private $accountPermissions;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $secondaryEmail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notifySecond = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $customInvoicing = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCompany = false;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $invoicingName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $invoicingAddress;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $invoicingIdent;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $invoicingCompanyReg;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $invoicingCompanyFiscal;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccountInvoice", mappedBy="createdBy")
     */
    private $createdInvoices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SmartReceipt", mappedBy="createdBy")
     */
    private $createdReceipts;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->classGroups = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->classOptionals = new ArrayCollection();
        $this->accountPermissions = new ArrayCollection();
        $this->createdInvoices = new ArrayCollection();
        $this->createdReceipts = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;

    }

    public function getUsertype(): ?string
    {
        // switch ($this->usertype) {
        //   case 'ROLE_ADMIN':
        //     return 'Administrator';
        //   case 'ROLE_PROF':
        //     return 'Profesor';
        //   case 'ROLE_PARENT':
        //     return 'Părinte';
        //   case 'ROLE_PUPIL':
        //     return 'Elev';
        // }
        return $this->usertype;
    }

    // function used for display purposes only
    public function getNormalized(): ?string
    {
        switch ($this->usertype) {
          case 'ROLE_ADMIN':
            return 'Administrator';
          case 'ROLE_CUSTOM':
            return 'Manager';
          case 'ROLE_PROF':
            return 'Profesor';
          case 'ROLE_PARENT':
            return 'Părinte';
          case 'ROLE_PUPIL':
            return 'Elev';
        }
        // return $this->usertype;
    }

    public function setUsertype(string $usertype): self
    {
        $this->usertype = $usertype;

        return $this;
    }

    public function getRoles()
    {
      return [
        $this->usertype
      ];
    }

    public function getSalt() {}

    public function eraseCredentials() {}

    public function serialize()
    {
      return serialize([
        $this->id,
        $this->username,
        $this->email,
        $this->password,
        $this->usertype
      ]);
    }

    public function unserialize($string)
    {
      list (
        $this->id,
        $this->username,
        $this->email,
        $this->password,
        $this->usertype
      ) = unserialize($string, ['allowed_classes' => false]);
    }

    /**
     * @return Collection|Enrollment[]
     */
    public function getEnrollmentsParent(): Collection
    {
        return $this->enrollmentsParent;
    }

    public function addEnrollmentParent(Enrollment $enrollment): self
    {
        if (!$this->enrollmentsParent->contains($enrollment)) {
            $this->enrollmentsParent[] = $enrollment;
            $enrollment->setIdParent($this);
        }

        return $this;
    }

    public function removeEnrollmentParent(Enrollment $enrollment): self
    {
        if ($this->enrollmentsChild->contains($enrollment)) {
            $this->enrollmentsChild->removeElement($enrollment);
            // set the owning side to null (unless already changed)
            if ($enrollment->getIdParent() === $this) {
                $enrollment->setIdParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Enrollment[]
     */
    public function getEnrollmentsChild(): Collection
    {
        return $this->enrollmentsChild;
    }

    public function getChildLatestEnroll(): ?Enrollment
    {
        $allEnrollments = $this->enrollmentsChild;
        $latest = new Enrollment();
        foreach ($allEnrollments as $theEnrollment) {
          if ($theEnrollment->getEnrollDate() > $latest->getEnrollDate() && $theEnrollment->getIsActive()) {
            $latest = $theEnrollment;
          }
        }
        return $latest;
    }

    public function addEnrollmentChild(Enrollment $enrollment): self
    {
        if (!$this->enrollmentsChild->contains($enrollment)) {
            $this->enrollmentsChild[] = $enrollment;
            $enrollment->setIdParent($this);
        }

        return $this;
    }

    public function removeEnrollmentChild(Enrollment $enrollment): self
    {
        if ($this->enrollmentsChild->contains($enrollment)) {
            $this->enrollmentsChild->removeElement($enrollment);
            // set the owning side to null (unless already changed)
            if ($enrollment->getIdParent() === $this) {
                $enrollment->setIdParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ClassGroup[]
     */
    public function getClassGroups(): Collection
    {
        return $this->classGroups;
    }

    public function addClassGroup(ClassGroup $classGroup): self
    {
        if (!$this->classGroups->contains($classGroup)) {
            $this->classGroups[] = $classGroup;
            $classGroup->setProfessor($this);
        }

        return $this;
    }

    public function removeClassGroup(ClassGroup $classGroup): self
    {
        if ($this->classGroups->contains($classGroup)) {
            $this->classGroups->removeElement($classGroup);
            // set the owning side to null (unless already changed)
            if ($classGroup->getProfessor() === $this) {
                $classGroup->setProfessor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Student[]
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->setUser($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
            // set the owning side to null (unless already changed)
            if ($student->getUser() === $this) {
                $student->setUser(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(int $order=0): ?string
    {
        if ($order == 1) {
            return $this->lastName.' '.$this->firstName;
        } else {
            return $this->firstName.' '.$this->lastName;
        }
    }

    public function getRoName(): ?string
    {
        return $this->lastName.' '.$this->firstName;
    }

    public function getPhoneNo(): ?string
    {
        return $this->phoneNo;
    }

    public function setPhoneNo(string $phoneNo): self
    {
        $this->phoneNo = $phoneNo;

        return $this;
    }

    /**
     * @return Collection|ClassOptional[]
     */
    public function getClassOptionals(): Collection
    {
        return $this->classOptionals;
    }

    public function addClassOptional(ClassOptional $classOptional): self
    {
        if (!$this->classOptionals->contains($classOptional)) {
            $this->classOptionals[] = $classOptional;
            $classOptional->setProfessor($this);
        }

        return $this;
    }

    public function removeClassOptional(ClassOptional $classOptional): self
    {
        if ($this->classOptionals->contains($classOptional)) {
            $this->classOptionals->removeElement($classOptional);
            // set the owning side to null (unless already changed)
            if ($classOptional->getProfessor() === $this) {
                $classOptional->setProfessor(null);
            }
        }

        return $this;
    }

    public function getGuardian(): ?Guardian
    {
        return $this->guardian;
    }

    public function setGuardian(?Guardian $guardian): self
    {
        $this->guardian = $guardian;

        return $this;
    }

    public function getGuardianacc(): ?Guardian
    {
        return $this->guardianacc;
    }

    public function setGuardianacc(Guardian $guardianacc): self
    {
        $this->guardianacc = $guardianacc;

        // set the owning side of the relation if necessary
        if ($this !== $guardianacc->getUser()) {
            $guardianacc->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|AccountPermission[]
     */
    public function getAccountPermissions(): Collection
    {
        return $this->accountPermissions;
    }

    public function addAccountPermission(AccountPermission $accountPermission): self
    {
        if (!$this->accountPermissions->contains($accountPermission)) {
            $this->accountPermissions[] = $accountPermission;
            $accountPermission->addUser($this);
        }

        return $this;
    }

    public function removeAccountPermission(AccountPermission $accountPermission): self
    {
        if ($this->accountPermissions->contains($accountPermission)) {
            $this->accountPermissions->removeElement($accountPermission);
            $accountPermission->removeUser($this);
        }

        return $this;
    }

    public function getSecondaryEmail(): ?string
    {
        return $this->secondaryEmail;
    }

    public function setSecondaryEmail(?string $secondaryEmail): self
    {
        $this->secondaryEmail = $secondaryEmail;

        return $this;
    }

    public function getNotifySecond(): ?bool
    {
        return $this->notifySecond;
    }

    public function setNotifySecond(bool $notifySecond): self
    {
        $this->notifySecond = $notifySecond;

        return $this;
    }

    public function getCustomInvoicing(): ?bool
    {
        return $this->customInvoicing;
    }

    public function setCustomInvoicing(bool $customInvoicing): self
    {
        $this->customInvoicing = $customInvoicing;

        return $this;
    }

    public function getIsCompany(): ?bool
    {
        return $this->isCompany;
    }

    public function setIsCompany(bool $isCompany): self
    {
        $this->isCompany = $isCompany;

        return $this;
    }

    public function getInvoicingName(): ?string
    {
        return $this->invoicingName;
    }

    public function setInvoicingName(?string $invoicingName): self
    {
        $this->invoicingName = $invoicingName;

        return $this;
    }

    public function getInvoicingAddress(): ?string
    {
        return $this->invoicingAddress;
    }

    public function setInvoicingAddress(?string $invoicingAddress): self
    {
        $this->invoicingAddress = $invoicingAddress;

        return $this;
    }

    public function getInvoicingIdent(): ?string
    {
        return $this->invoicingIdent;
    }

    public function setInvoicingIdent(?string $invoicingIdent): self
    {
        $this->invoicingIdent = $invoicingIdent;

        return $this;
    }

    public function getInvoicingCompanyReg(): ?string
    {
        return $this->invoicingCompanyReg;
    }

    public function setInvoicingCompanyReg(?string $invoicingCompanyReg): self
    {
        $this->invoicingCompanyReg = $invoicingCompanyReg;

        return $this;
    }

    public function getInvoicingCompanyFiscal(): ?string
    {
        return $this->invoicingCompanyFiscal;
    }

    public function setInvoicingCompanyFiscal(?string $invoicingCompanyFiscal): self
    {
        $this->invoicingCompanyFiscal = $invoicingCompanyFiscal;

        return $this;
    }

    /**
     * @return Collection|AccountInvoice[]
     */
    public function getCreatedInvoices(): Collection
    {
        return $this->createdInvoices;
    }

    public function addCreatedInvoice(AccountInvoice $createdInvoice): self
    {
        if (!$this->createdInvoices->contains($createdInvoice)) {
            $this->createdInvoices[] = $createdInvoice;
            $createdInvoice->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedInvoice(AccountInvoice $createdInvoice): self
    {
        if ($this->createdInvoices->contains($createdInvoice)) {
            $this->createdInvoices->removeElement($createdInvoice);
            // set the owning side to null (unless already changed)
            if ($createdInvoice->getCreatedBy() === $this) {
                $createdInvoice->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SmartReceipt[]
     */
    public function getCreatedReceipts(): Collection
    {
        return $this->createdReceipts;
    }

    public function addCreatedReceipt(SmartReceipt $createdReceipt): self
    {
        if (!$this->createdReceipts->contains($createdReceipt)) {
            $this->createdReceipts[] = $createdReceipt;
            $createdReceipt->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedReceipt(SmartReceipt $createdReceipt): self
    {
        if ($this->createdReceipts->contains($createdReceipt)) {
            $this->createdReceipts->removeElement($createdReceipt);
            // set the owning side to null (unless already changed)
            if ($createdReceipt->getCreatedBy() === $this) {
                $createdReceipt->setCreatedBy(null);
            }
        }

        return $this;
    }


}
