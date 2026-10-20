<?php

namespace App\Twig;

use App\Entity\Message;
use App\Entity\ProfileLike;
use App\Entity\ProfileVisit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class NotificationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User || !$user->getProfile()) {
            return ['notificationCounts' => ['messages' => 0, 'likesAndVisits' => 0]];
        }

        $messages = (int) $this->entityManager->getRepository(Message::class)->createQueryBuilder('m')
            ->select('COUNT(m.id)')->where('m.receiver = :user')->andWhere('m.readAt IS NULL')
            ->setParameter('user', $user)->getQuery()->getSingleScalarResult();
        $likes = (int) $this->entityManager->getRepository(ProfileLike::class)->createQueryBuilder('l')
            ->select('COUNT(l.id)')->where('l.liked = :profile')->andWhere('l.seenAt IS NULL')
            ->setParameter('profile', $user->getProfile())->getQuery()->getSingleScalarResult();
        $visits = (int) $this->entityManager->getRepository(ProfileVisit::class)->createQueryBuilder('v')
            ->select('COUNT(v.id)')->where('v.visited = :profile')->andWhere('v.seenAt IS NULL')
            ->setParameter('profile', $user->getProfile())->getQuery()->getSingleScalarResult();

        return ['notificationCounts' => ['messages' => $messages, 'likesAndVisits' => $likes + $visits]];
    }
}
