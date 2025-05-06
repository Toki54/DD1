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

 /**
  * Récupère toutes les conversations d'un utilisateur en excluant les messages supprimés
  */
 public function findUserConversations(User $user): array
 {
  $qb = $this->createQueryBuilder('m')
   ->where('m.sender = :user OR m.receiver = :user')
   ->setParameter('user', $user)
   ->orderBy('m.sentAt', 'DESC');

  $messages      = $qb->getQuery()->getResult();
  $conversations = [];

  // Récupérer les conversations supprimées
  $deletedConversations = $this->entityManager->getRepository(DeletedConversation::class)
   ->findBy(['user' => $user]);

  $deletedWithTimestamps = [];
  foreach ($deletedConversations as $dc) {
   $deletedWithTimestamps[$dc->getDeletedWith()->getId()] = $dc->getDeletedAt();
  }

  foreach ($messages as $message) {
   $interlocutor   = ($message->getSender() === $user) ? $message->getReceiver() : $message->getSender();
   $interlocutorId = $interlocutor->getId();

   // Vérifier si la conversation a été supprimée et ignorer les messages anciens
   if (isset($deletedWithTimestamps[$interlocutorId]) && $message->getSentAt() <= $deletedWithTimestamps[$interlocutorId]) {
    continue;
   }

   if (!isset($conversations[$interlocutorId])) {
    $conversations[$interlocutorId] = [
     'user'     => $interlocutor,
     'messages' => [],
    ];
   }

   $conversations[$interlocutorId]['messages'][] = $message;
  }

  return $conversations;
 }

 /**
  * Récupère tous les messages d'un utilisateur en tant qu'expéditeur ou destinataire
  */
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
  * Récupère les derniers messages entre deux utilisateurs
  */
 public function findLatestMessages(User $user1, User $user2, int $limit = 2): array
 {
  return $this->createQueryBuilder('m')
   ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
   ->setParameter('user1', $user1)
   ->setParameter('user2', $user2)
   ->orderBy('m.sentAt', 'DESC')
   ->setMaxResults($limit)
   ->getQuery()
   ->getResult();
 }

 /**
  * Récupère les messages d'une conversation en prenant en compte les suppressions
  */
 public function findByConversation(User $user, User $receiver): array
 {
  $deletedConversation = $this->entityManager->getRepository(DeletedConversation::class)
   ->findOneBy([
    'user'        => $user,
    'deletedWith' => $receiver,
   ]);

  $qb = $this->createQueryBuilder('m')
   ->where('(m.sender = :user AND m.receiver = :receiver) OR (m.sender = :receiver AND m.receiver = :user)')
   ->setParameter('user', $user)
   ->setParameter('receiver', $receiver)
   ->orderBy('m.sentAt', 'ASC');

  if ($deletedConversation) {
   $qb->andWhere('m.sentAt > :deletedAt')
    ->setParameter('deletedAt', $deletedConversation->getDeletedAt());
  }

  return $qb->getQuery()->getResult();
 }

 /**
  * Vérifie si un utilisateur a accepté une demande de chat
  */
 public function hasAcceptedChat(User $user1, User $user2): bool
 {
  return (bool) $this->createQueryBuilder('m')
   ->where('(m.sender = :user1 AND m.receiver = :user2 AND m.content = :accepted AND m.isChatRequest = true)')
   ->orWhere('(m.sender = :user2 AND m.receiver = :user1 AND m.content = :accepted AND m.isChatRequest = true)')
   ->setParameter('user1', $user1)
   ->setParameter('user2', $user2)
   ->setParameter('accepted', 'ACCEPTED')
   ->setMaxResults(1)
   ->getQuery()
   ->getOneOrNullResult();
 }
}
