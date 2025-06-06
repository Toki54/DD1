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

    #[ORM\ManyToOne(targetEntity: UserProfile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserProfile $visited = null;

    #[ORM\ManyToOne(targetEntity: UserProfile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserProfile $visitor = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $visitedAt;

    public function __construct()
    {
        $this->visitedAt = new \DateTime();
    }


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of visited
     */ 
    public function getVisited()
    {
        return $this->visited;
    }

    
    public function setVisited($visited)
    {
        $this->visited = $visited;

        return $this;
    }

    
    public function getVisitor()
    {
        return $this->visitor;
    }

    
    public function setVisitor($visitor)
    {
        $this->visitor = $visitor;

        return $this;
    }

    
    public function getVisitedAt()
    {
        return $this->visitedAt;
    }

    
     
    public function setVisitedAt($visitedAt)
    {
        $this->visitedAt = $visitedAt;

        return $this;
    }
}

