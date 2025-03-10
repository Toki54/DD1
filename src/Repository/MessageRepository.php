<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class MessageRepository extends ServiceEntityRepository
{
 public function __construct(ManagerRegistry $registry)
 {
  parent::__construct($registry, Message::class);
 }

 /**
     * Récupère les conversations en regroupant les messages entre deux utilisateurs
     */
    public function findUserConversations(User $user): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('m.sentAt', 'DESC');

        $messages = $qb->getQuery()->getResult();
        $conversations = [];

        foreach ($messages as $message) {
            $interlocutor = ($message->getSender() === $user) ? $message->getReceiver() : $message->getSender();
            $interlocutorId = $interlocutor->getId();

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

 /**
  * Récupère les messages envoyés ou reçus par un utilisateur
  */
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
