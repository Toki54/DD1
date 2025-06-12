<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProfileLike
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column]
 private ?int $id = null;

 // Profil qui like
 #[ORM\ManyToOne(targetEntity: UserProfile::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?UserProfile $liker = null;

 // Profil qui est liké
 #[ORM\ManyToOne(targetEntity: UserProfile::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?UserProfile $liked = null;

 #[ORM\Column(type: 'datetime')]
 private \DateTimeInterface $likedAt;

 #[ORM\Column(type: 'boolean')]
private bool $seen = false;

 public function __construct()
 {
  $this->likedAt = new \DateTime();
 }

 public function getId(): ?int
 {
  return $this->id;
 }

 public function getLiker(): ?UserProfile
 {
  return $this->liker;
 }

 public function setLiker(UserProfile $liker): self
 {
  $this->liker = $liker;

  return $this;
 }

 public function getLiked(): ?UserProfile
 {
  return $this->liked;
 }

 public function setLiked(UserProfile $liked): self
 {
  $this->liked = $liked;

  return $this;
 }

 public function getLikedAt(): \DateTimeInterface
 {
  return $this->likedAt;
 }

 public function setLikedAt(\DateTimeInterface $likedAt): self
 {
  $this->likedAt = $likedAt;

  return $this;
 }

 public function isSeen(): bool
{
    return $this->seen;
}

public function setSeen(bool $seen): self
{
    $this->seen = $seen;
    return $this;
}
}
