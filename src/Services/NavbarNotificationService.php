<?php


namespace App\Service;

use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;

class NavbarNotificationService
{
 private $messageRepository;
 private $security;

 public function __construct(MessageRepository $messageRepository, Security $security)
 {
  $this->messageRepository = $messageRepository;
  $this->security          = $security;
 }

 public function getUnreadMessagesCount(): int
 {
  $user = $this->security->getUser();

  if (!$user) {
   return 0;
  }

  return $this->messageRepository->countUnreadMessages($user);
 }
}
