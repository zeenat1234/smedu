<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

//Validation Classes
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="This e-mail address is already in use"
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     message="This username is already in use"
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
     * @Assert\NotBlank(message = "This field can not be blank")
     * @Assert\Length(
     *     min=4, minMessage = "The username must be at least '{{ limit }}' characters long",
     *     max=32, maxMessage = "The username can NOT contain more than '{{ limit }}' characters"
     * )
     * @Assert\Type(
     *     type="alpha",
     *     message="The username can only contain letters"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     * @Assert\NotBlank(message = "This field can not be blank")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email",
     *     checkMX = true
     * )
     */
    private $email;

    /**
    * @ORM\Column(type="string", length=128)
    * @Assert\Length(
    *     min=8, minMessage = "The password must be at least '{{ limit }}' characters long",
    *     max=32, maxMessage = "The password can NOT contain more than '{{ limit }}' characters"
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
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=32)
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

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->classGroups = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->classOptionals = new ArrayCollection();
    }

    //TODO: Add firstname, lastname, dateofbirth, id_parent (manyToOne)

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


}
