<?php

namespace App\Repository;

use App\Entity\ProfileLike;
use App\Entity\UserProfile;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ProfileLike>
 */
class ProfileLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfileLike::class);
    }

    public function countUnreadLikes(UserProfile $profile): int
{
    return $this->createQueryBuilder('pl')
        ->select('COUNT(pl.id)')
        ->where('pl.liked = :profile')
        ->andWhere('pl.seen = false')
        ->setParameter('profile', $profile)
        ->getQuery()
        ->getSingleScalarResult();
}
}
