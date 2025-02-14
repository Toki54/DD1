<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class UserProfile
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column]
 private ?int $id = null;

 #[ORM\Column(type: 'string', length: 50, nullable: true)]
 private ?string $sex = null;

 #[ORM\Column(type: 'string', length: 100, nullable: true)]
 private ?string $situation = null;

 #[ORM\Column(type: 'string', length: 100, nullable: true)]
 private ?string $research = null;

 #[ORM\Column(type: 'text', nullable: true)]
 private ?string $biography = null;

 #[ORM\Column(type: 'string', length: 255, nullable: true)]
 private ?string $avatar = null;

 #[Assert\File(
  maxSize: '5M',
  mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
  mimeTypesMessage: 'Please upload a valid image file (JPEG, PNG, GIF)'
 )]
 private ?File $avatarFile = null; // Propriété temporaire pour le fichier uploadé

 #[ORM\Column(type: 'json', nullable: true)]
 private ?array $photos = [];

 #[Assert\All([
   new Assert\File(
    maxSize: '5M',
    mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
    mimeTypesMessage: 'Each photo must be a valid image file (JPEG, PNG, GIF).'
   ),
  ])]
 private array $photoFiles = []; // Propriété temporaire pour gérer les fichiers uploadés

 #[ORM\OneToOne(inversedBy: 'profile', targetEntity: User::class, cascade: ['persist', 'remove'])]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $user = null;

 // 🔹 Getters et Setters 🔹

 public function getId(): ?int
 {
  return $this->id;
 }

 public function getSex(): ?string
 {
  return $this->sex;
 }

 public function setSex(?string $sex): static
 {
  $this->sex = $sex;
  return $this;
 }

 public function getSituation(): ?string
 {
  return $this->situation;
 }

 public function setSituation(?string $situation): static
 {
  $this->situation = $situation;
  return $this;
 }

 public function getResearch(): ?string
 {
  return $this->research;
 }

 public function setResearch(?string $research): static
 {
  $this->research = $research;
  return $this;
 }

 public function getBiography(): ?string
 {
  return $this->biography;
 }

 public function setBiography(?string $biography): static
 {
  $this->biography = $biography;
  return $this;
 }

 public function getAvatar(): ?string
 {
  return $this->avatar;
 }

 public function setAvatar(?string $avatar): static
 {
  $this->avatar = $avatar;
  return $this;
 }

 public function getAvatarFile(): ?File
 {
  return $this->avatarFile;
 }

 public function setAvatarFile(?File $avatarFile): static
 {
  $this->avatarFile = $avatarFile;
  return $this;
 }

 public function getPhotos(): ?array
 {
  return $this->photos;
 }

 public function setPhotos(?array $photos): static
 {
  $this->photos = $photos;
  return $this;
 }

 public function getPhotoFiles(): array
 {
  return $this->photoFiles;
 }

 public function setPhotoFiles(array $photoFiles): static
 {
  $this->photoFiles = $photoFiles;
  return $this;
 }

 public function getUser(): ?User
 {
  return $this->user;
 }

 public function setUser(User $user): static
 {
  $this->user = $user;
  return $this;
 }
}
