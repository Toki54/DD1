<?php


namespace App\Service;

use App\Repository\MessageRepository;
use App\Repository\ProfileLikeRepository;
use Symfony\Bundle\SecurityBundle\Security;

class NavbarNotificationService
{
 private $messageRepository;
 private $profileLikeRepository;
 private $security;

 public function __construct(
  MessageRepository $messageRepository,
  ProfileLikeRepository $profileLikeRepository,
  Security $security
 ) {
  $this->messageRepository     = $messageRepository;
  $this->profileLikeRepository = $profileLikeRepository;
  $this->security              = $security;
 }

 public function getUnreadMessagesCount(): int
 {
  $user = $this->security->getUser();
  return $user ? $this->messageRepository->countUnreadMessages($user) : 0;
 }

 public function getUnreadLikesCount(): int
 {
  $user = $this->security->getUser();
  if (!$user || !$user->getProfile()) {
   return 0;
  }
  return $this->profileLikeRepository->countUnreadLikes($user->getProfile());
 }

}
