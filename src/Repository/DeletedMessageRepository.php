<?php

namespace App\Repository;

use App\Entity\DeletedMessage;
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
}
