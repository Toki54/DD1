<?php

namespace App\Services;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MessageQuotaService
{
    public const DAILY_LIMIT_FREE = 10;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function getDailyLimitFor(User $user): int
    {
        return self::DAILY_LIMIT_FREE;
    }

    public function countSentToday(User $user): int
    {
        $start = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
        $end   = $start->modify('+1 day');

        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from(Message::class, 'm')
            ->andWhere('m.sender = :user')
            ->andWhere('m.isChatRequest = false')
            ->andWhere('m.sentAt >= :start')
            ->andWhere('m.sentAt < :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function remainingToday(User $user): int
    {
        return max(0, self::DAILY_LIMIT_FREE - $this->countSentToday($user));
    }

    public function hasReachedLimit(User $user): bool
    {
        return $this->remainingToday($user) <= 0;
    }
}
