<?php

namespace App\Repository;

use App\Entity\DeletedConversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeletedConversationRepository extends ServiceEntityRepository
{
 public function __construct(ManagerRegistry $registry)
 {
  parent::__construct($registry, DeletedConversation::class);
 }

 public function deleteConversation(User $user, User $deletedWith): void
 {
  $entityManager = $this->getEntityManager();

  $deletedConversation = $this->findOneBy([
   'user'        => $user,
   'deletedWith' => $deletedWith,
  ]);

  if (!$deletedConversation) {
   $deletedConversation = new DeletedConversation();
   $deletedConversation->setUser($user);
   $deletedConversation->setDeletedWith($deletedWith);
  }

  $deletedConversation->setDeletedAt(new \DateTimeImmutable());

  $entityManager->persist($deletedConversation);
  $entityManager->flush();
 }
}
