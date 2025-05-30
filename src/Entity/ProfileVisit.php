<?php

namespace App\Entity;

use App\Repository\ProfileVisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileVisitRepository::class)]
class ProfileVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $visitor = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $visited = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $visitedAt = null;

    public function getId(): ?int { return $this->id; }

    public function getVisitor(): ?User { return $this->visitor; }
    public function setVisitor(?User $visitor): self { $this->visitor = $visitor; return $this; }

    public function getVisited(): ?User { return $this->visited; }
    public function setVisited(?User $visited): self { $this->visited = $visited; return $this; }

    public function getVisitedAt(): ?\DateTimeInterface { return $this->visitedAt; }
    public function setVisitedAt(\DateTimeInterface $visitedAt): self { $this->visitedAt = $visitedAt; return $this; }
}
