<?php

namespace App\Twig;

use App\Repository\UserRepository;
use App\Services\NavbarNotificationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
 private NavbarNotificationService $notifier;
 private UserRepository $userRepository;

 public function __construct(NavbarNotificationService $notifier, UserRepository $userRepository)
 {
  $this->notifier       = $notifier;
  $this->userRepository = $userRepository;
 }

 public function getFunctions(): array
 {
  return [
   new TwigFunction('unread_messages_count', [$this->notifier, 'getUnreadMessagesCount']),
   new TwigFunction('unread_likes_count', [$this, 'getUnreadLikesCount']),
   new TwigFunction('total_users_count', [$this, 'getTotalUsersCount']),
  ];
 }

 public function getUnreadLikesCount(): int
 {
  return $this->notifier->getUnreadLikesCount();
 }

 public function getTotalUsersCount(): int
 {
  return $this->userRepository->count([]);
 }
}
