<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column]
 private ?int $id = null;

 #[ORM\OneToOne(inversedBy: 'subscription', targetEntity: User::class)]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $user = null;

 #[ORM\Column(type: 'datetime')]
 #[Assert\NotNull]
 private ?\DateTimeInterface $startDate = null;

 #[ORM\Column(type : 'datetime')]
 #[Assert\NotNull]
 private ?\DateTimeInterface $endDate = null;

 #[ORM\Column(length : 50)]
 #[Assert\NotBlank]
 private ?string $plan = null;

 #[ORM\Column(type: 'float')]
 #[Assert\PositiveOrZero]
 private float $price = 0.0;

 #[ORM\Column(type: 'boolean')]
 private bool $active = true;

 #[ORM\Column(length: 255, nullable: true)]
 private ?string $stripeSubscriptionId = null;

 #[ORM\Column(type: 'datetime')]
 private ?\DateTimeInterface $createdAt = null;

 #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

 public function __construct()
 {
  $this->createdAt = new \DateTime();
 }

 public function getId(): ?int
 {
  return $this->id;
 }

 public function getUser(): ?User
 {
  return $this->user;
 }

 public function setUser(?User $user): static
 {
  $this->user = $user;
  return $this;
 }

 public function getStartDate(): ?\DateTimeInterface
 {
  return $this->startDate;
 }

 public function setStartDate(\DateTimeInterface $startDate): static
 {
  $this->startDate = $startDate;
  return $this;
 }

 public function getEndDate(): ?\DateTimeInterface
 {
  return $this->endDate;
 }

 public function setEndDate(\DateTimeInterface $endDate): static
 {
  $this->endDate = $endDate;
  return $this;
 }

 public function getPlan(): ?string
 {
  return $this->plan;
 }

 public function setPlan(string $plan): static
 {
  $this->plan = $plan;
  return $this;
 }

 public function getPrice(): float
 {
  return $this->price;
 }

 public function setPrice(float $price): static
 {
  $this->price = $price;
  return $this;
 }

 public function isActive(): bool
 {
  return $this->active;
 }

 public function setActive(bool $active): static
 {
  $this->active = $active;
  return $this;
 }

 public function getStripeSubscriptionId(): ?string
 {
  return $this->stripeSubscriptionId;
 }

 public function setStripeSubscriptionId(?string $stripeSubscriptionId): static
 {
  $this->stripeSubscriptionId = $stripeSubscriptionId;
  return $this;
 }

 public function getCreatedAt(): ?\DateTimeInterface
 {
  return $this->createdAt;
 }

 public function setCreatedAt(\DateTimeInterface $createdAt): static
 {
  $this->createdAt = $createdAt;
  return $this;
 }

 public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;
        return $this;
    }
}
