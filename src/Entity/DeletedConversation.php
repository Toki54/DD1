<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\DeletedConversationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeletedConversationRepository::class)]
class DeletedConversation
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 private ?int $id = null;

 #[ORM\ManyToOne(targetEntity: User::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $user = null;

 #[ORM\ManyToOne(targetEntity: User::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $deletedWith = null;

 public function getId(): ?int
 {
  return $this->id;
 }

 public function getUser(): ?User
 {
  return $this->user;
 }

 public function setUser(?User $user): self
 {
  $this->user = $user;
  return $this;
 }

 public function getDeletedWith(): ?User
 {
  return $this->deletedWith;
 }

 public function setDeletedWith(?User $deletedWith): self
 {
  $this->deletedWith = $deletedWith;
  return $this;
 }
}
