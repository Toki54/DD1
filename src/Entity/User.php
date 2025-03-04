<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 private ?int $id = null;

 #[ORM\Column(length: 180)]
 private ?string $email = null;

 /**
  * @var list<string> The user roles
  */
 #[ORM\Column(type: 'json')]
 private array $roles = [];

 /**
  * @var string The hashed password
  */
 #[ORM\Column]
 private ?string $password = null;

 #[ORM\Column(length: 80)]
 private ?string $pseudo = null;

 #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist', 'remove'])]
 private ?UserProfile $profile = null;

 public function getProfile(): ?UserProfile
 {
  return $this->profile;
 }

 public function setProfile(?UserProfile $profile): static
 {
  $this->profile = $profile;
  return $this;
 }

 public function getId(): ?int
 {
  return $this->id;
 }

 public function getEmail(): ?string
 {
  return $this->email;
 }

 public function setEmail(string $email): static
 {
  $this->email = $email;

  return $this;
 }

 /**
  * A visual identifier that represents this user.
  *
  * @see UserInterface
  */
 public function getUserIdentifier(): string
 {
  return (string) $this->email;
 }

 /**
  * @see UserInterface
  *
  * @return list<string>
  */
 public function getRoles(): array
 {
  $roles = $this->roles;
  // guarantee every user at least has ROLE_USER
  $roles[] = 'ROLE_USER';

  return array_unique($roles);
 }

 public function isAdmin(): bool
 {
  return in_array('ROLE_ADMIN', $this->roles, true);
 }

 /**
  * @param list<string> $roles
  */
 public function setRoles(array $roles): static
 {
  $this->roles = $roles;

  return $this;
 }

 /**
  * @see PasswordAuthenticatedUserInterface
  */
 public function getPassword(): ?string
 {
  return $this->password;
 }

 public function setPassword(string $password): static
 {
  $this->password = $password;

  return $this;
 }

 /**
  * @see UserInterface
  */
 public function eraseCredentials(): void
 {
  // If you store any temporary, sensitive data on the user, clear it here
  // $this->plainPassword = null;
 }

 /**
  * Get the value of pseudo
  */
 public function getPseudo(): ?string
 {
  return $this->pseudo;
 }

 /**
  * Set the value of pseudo
  */
 public function setPseudo(string $pseudo): static
 {
  $this->pseudo = $pseudo;

  return $this;
 }
}
