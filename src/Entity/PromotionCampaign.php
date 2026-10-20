<?php

namespace App\Entity;

use App\Repository\PromotionCampaignRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionCampaignRepository::class)]
class PromotionCampaign
{
    public const MEN_PREMIUM = 'men_premium';

    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column]
    private int $startUserId = 0;

    public function __construct(string $name = self::MEN_PREMIUM, int $startUserId = 0)
    {
        $this->name = $name;
        $this->startUserId = $startUserId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartUserId(): int
    {
        return $this->startUserId;
    }

    public function setStartUserId(int $startUserId): static
    {
        $this->startUserId = $startUserId;

        return $this;
    }
}
