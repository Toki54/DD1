<?php


namespace App\Twig;

use App\Service\NavbarNotificationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
 private $notifier;

 public function __construct(NavbarNotificationService $notifier)
 {
  $this->notifier = $notifier;
 }

 public function getFunctions(): array
 {
  return [
   new TwigFunction('unread_messages_count', [$this->notifier, 'getUnreadMessagesCount']),
  ];
 }
}
