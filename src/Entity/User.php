<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
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
     * @ORM\Column(type="text", length=32)
     */
    private $username;

    /**
     * @ORM\Column(type="text", length=254)
     */
    private $email;

    /**
    * @ORM\Column(type="text", length=32)
    */
    private $password;

    /**
     * @ORM\Column(type="text", length=16)
     */
    private $usertype;

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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUsertype(): ?string
    {
        return $this->usertype;
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
}
