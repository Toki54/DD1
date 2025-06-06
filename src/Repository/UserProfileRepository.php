<?php

namespace App\Repository;

use App\Entity\UserProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserProfile>
 *
 * @method UserProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProfile[]    findAll()
 * @method UserProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProfileRepository extends ServiceEntityRepository
{
 public function __construct(ManagerRegistry $registry)
 {
  parent::__construct($registry, UserProfile::class);
 }

 /**
  * Récupère les profils filtrés selon les critères passés en paramètres.
  */
 public function findFilteredProfiles(?array $sex = [], ?array $situation = [], ?string $city = null, ?array $research = []): array
 {
  $qb = $this->createQueryBuilder('p');

  if (!empty($sex)) {
   $qb->andWhere('p.sex IN (:sex)')
    ->setParameter('sex', $sex);
  }

  if (!empty($situation)) {
   $qb->andWhere('p.situation IN (:situation)')
    ->setParameter('situation', $situation);
  }

  if (!empty($city)) {
   $qb->andWhere('p.city = :city')
    ->setParameter('city', $city);
  }

  if (!empty($research)) {
   $qb->andWhere('p.research IN (:research)')
    ->setParameter('research', $research);
  }

  return $qb->getQuery()->getResult();
 }
}
