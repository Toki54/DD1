<?php

// src/Entity/Message.php
namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\MessageRepository')]
class Message
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 private ?int $id = null;

 #[ORM\Column(type: 'text')]
 private ?string $content = null;

 #[ORM\ManyToOne(targetEntity: User::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $sender = null;

 #[ORM\ManyToOne(targetEntity: User::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $receiver = null;

 #[ORM\Column(type: 'datetime')]
 private \DateTimeInterface $sentAt;

 public function __construct()
 {
  $this->sentAt = new \DateTime();
 }

 // Getters et setters

 public function getId(): ?int
 {
  return $this->id;
 }

 public function getContent(): ?string
 {
  return $this->content;
 }

 public function setContent(string $content): self
 {
  $this->content = $content;

  return $this;
 }

 public function getSender(): ?User
 {
  return $this->sender;
 }

 public function setSender(?User $sender): self
 {
  $this->sender = $sender;

  return $this;
 }

 public function getReceiver(): ?User
 {
  return $this->receiver;
 }

 public function setReceiver(?User $receiver): self
 {
  $this->receiver = $receiver;

  return $this;
 }

 public function getSentAt(): \DateTimeInterface
 {
  return $this->sentAt;
 }

 public function setSentAt(\DateTimeInterface $sentAt): self
 {
  $this->sentAt = $sentAt;

  return $this;
 }
}
