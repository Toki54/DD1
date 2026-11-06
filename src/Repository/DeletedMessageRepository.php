<?php

namespace App\Repository;

use App\Entity\DeletedMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeletedMessage>
 */
class DeletedMessageRepository extends ServiceEntityRepository
{
 public function __construct(ManagerRegistry $registry)
 {
  parent::__construct($registry, DeletedMessage::class);
 }

 /**
  * @return list<int>
  */
 public function findParticipantIds(): array
 {
  $rows = $this->createQueryBuilder('m')
   ->select('IDENTITY(m.sender) AS senderId, IDENTITY(m.receiver) AS receiverId')
   ->getQuery()
   ->getScalarResult();

  $participantIds = [];
  foreach ($rows as $row) {
   foreach (['senderId', 'receiverId'] as $key) {
    if (isset($row[$key])) {
     $participantIds[(int) $row[$key]] = true;
    }
   }
  }

  return array_keys($participantIds);
 }

 /**
  * @return list<DeletedMessage>
  */
 public function findBySenderOrReceiver(User $user): array
 {
  return $this->createQueryBuilder('m')
   ->where('m.sender = :user OR m.receiver = :user')
   ->setParameter('user', $user)
   ->orderBy('m.deletedAt', 'DESC')
   ->getQuery()
   ->getResult();
 }
}
