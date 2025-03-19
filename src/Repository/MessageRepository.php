<?php

namespace App\Repository;

use App\Entity\DeletedConversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
 private EntityManagerInterface $entityManager;

 public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
 {
  parent::__construct($registry, Message::class);
  $this->entityManager = $entityManager;
 }

public function findUserConversations(User $user): array
{
    $qb = $this->createQueryBuilder('m')
        ->where('m.sender = :user OR m.receiver = :user')
        ->setParameter('user', $user)
        ->orderBy('m.sentAt', 'DESC');

    $messages = $qb->getQuery()->getResult();
    $conversations = [];

    // Récupérer les conversations supprimées avec l'EntityManager correct
    $deletedConversations = $this->entityManager->getRepository(DeletedConversation::class)
        ->findBy(['user' => $user]);

    $deletedWithTimestamps = [];
    foreach ($deletedConversations as $dc) {
        $deletedWithTimestamps[$dc->getDeletedWith()->getId()] = $dc->getDeletedAt();
    }

    foreach ($messages as $message) {
        $interlocutor = ($message->getSender() === $user) ? $message->getReceiver() : $message->getSender();
        $interlocutorId = $interlocutor->getId();

        // Vérifier si la conversation a été supprimée et ignorer seulement les anciens messages
        if (isset($deletedWithTimestamps[$interlocutorId])) {
            $deletedAt = $deletedWithTimestamps[$interlocutorId];

            if ($message->getSentAt() <= $deletedAt) {
                continue; // On ignore seulement les anciens messages
            }
        }

        if (!isset($conversations[$interlocutorId])) {
            $conversations[$interlocutorId] = [
                'user' => $interlocutor,
                'messages' => [],
            ];
        }

        $conversations[$interlocutorId]['messages'][] = $message;
    }

    return $conversations;
}




 public function findBySenderOrReceiver(User $user): array
 {
  return $this->createQueryBuilder('m')
   ->where('m.sender = :user OR m.receiver = :user')
   ->setParameter('user', $user)
   ->orderBy('m.sentAt', 'DESC')
   ->getQuery()
   ->getResult();
 }

 /**
  * Récupère les derniers messages échangés entre deux utilisateurs
  */
 public function findLatestMessages(User $user1, User $user2, int $limit = 2): array
 {
  return $this->createQueryBuilder('m')
   ->where('m.sender IN (:users) AND m.receiver IN (:users)')
   ->setParameter('users', [$user1, $user2])
   ->orderBy('m.sentAt', 'DESC')
   ->setMaxResults($limit)
   ->getQuery()
   ->getResult();
 }

 public function findByConversation(User $user, User $receiver): array
 {
  return $this->createQueryBuilder('m')
   ->where('(m.sender = :user AND m.receiver = :receiver) OR (m.sender = :receiver AND m.receiver = :user)')
   ->setParameter('user', $user)
   ->setParameter('receiver', $receiver)
   ->orderBy('m.sentAt', 'ASC')
   ->getQuery()
   ->getResult();
 }

 public function hasAcceptedChat(User $user1, User $user2): bool
 {
  $result = $this->createQueryBuilder('m')
   ->where('(m.sender = :user1 AND m.receiver = :user2 AND m.content = :accepted AND m.isChatRequest = true)')
   ->orWhere('(m.sender = :user2 AND m.receiver = :user1 AND m.content = :accepted AND m.isChatRequest = true)')
   ->setParameter('user1', $user1)
   ->setParameter('user2', $user2)
   ->setParameter('accepted', 'ACCEPTED')
   ->setMaxResults(1)
   ->getQuery()
   ->getOneOrNullResult();

  return $result !== null;
 }

 

}

