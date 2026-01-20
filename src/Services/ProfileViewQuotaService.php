<?php

namespace App\Services;

use App\Entity\ProfileVisit;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;

class ProfileViewQuotaService
{
    public const DAILY_LIMIT_FREE = 10;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function getDailyLimit(): int
    {
        return self::DAILY_LIMIT_FREE;
    }

    /**
     * Nombre de profils DISTINCTS vus aujourd'hui par le visiteur.
     */
    public function countDistinctProfilesViewedToday(UserProfile $visitorProfile): int
    {
        $start = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
        $end   = $start->modify('+1 day');

        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(DISTINCT IDENTITY(v.visited))')
            ->from(ProfileVisit::class, 'v')
            ->andWhere('v.visitor = :visitor')
            ->andWhere('v.visitedAt >= :start')
            ->andWhere('v.visitedAt < :end')
            ->setParameter('visitor', $visitorProfile)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Est-ce que le visiteur a déjà vu CE profil aujourd'hui ?
     */
    public function hasViewedToday(UserProfile $visitorProfile, UserProfile $visitedProfile): bool
    {
        $start = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
        $end   = $start->modify('+1 day');

        $count = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(v.id)')
            ->from(ProfileVisit::class, 'v')
            ->andWhere('v.visitor = :visitor')
            ->andWhere('v.visited = :visited')
            ->andWhere('v.visitedAt >= :start')
            ->andWhere('v.visitedAt < :end')
            ->setParameter('visitor', $visitorProfile)
            ->setParameter('visited', $visitedProfile)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function remainingToday(UserProfile $visitorProfile): int
    {
        $limit = $this->getDailyLimit();
        $used  = $this->countDistinctProfilesViewedToday($visitorProfile);

        return max(0, $limit - $used);
    }

    /**
     * Bloqué uniquement si limite atteinte ET que ce profil n'a pas déjà été vu aujourd'hui.
     */
    public function isBlockedForNewProfileToday(UserProfile $visitorProfile, UserProfile $visitedProfile): bool
    {
        if ($this->hasViewedToday($visitorProfile, $visitedProfile)) {
            return false;
        }

        return $this->remainingToday($visitorProfile) <= 0;
    }
}
