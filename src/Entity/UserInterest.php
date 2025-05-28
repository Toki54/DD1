<?php

namespace App\Entity;

use App\Repository\UserInterestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserInterestRepository::class)]
class UserInterest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $sourceUser = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $targetUser = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Get the value of sourceUser
     */ 
    public function getSourceUser()
    {
        return $this->sourceUser;
    }

    /**
     * Set the value of sourceUser
     *
     * @return  self
     */ 
    public function setSourceUser($sourceUser)
    {
        $this->sourceUser = $sourceUser;

        return $this;
    }

    /**
     * Get the value of targetUser
     */ 
    public function getTargetUser()
    {
        return $this->targetUser;
    }

    /**
     * Set the value of targetUser
     *
     * @return  self
     */ 
    public function setTargetUser($targetUser)
    {
        $this->targetUser = $targetUser;

        return $this;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
