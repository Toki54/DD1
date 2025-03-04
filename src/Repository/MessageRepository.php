<?php

// src/Repository/MessageRepository.php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
 public function __construct(ManagerRegistry $registry)
 {
  parent::__construct($registry, Message::class);
 }

 public function findBySenderOrReceiver($user)
 {
  return $this->createQueryBuilder('m')
   ->where('m.sender = :user OR m.receiver = :user')
   ->setParameter('user', $user)
   ->orderBy('m.sentAt', 'DESC')
   ->getQuery()
   ->getResult();
 }
}
