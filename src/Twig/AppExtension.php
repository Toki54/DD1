<?php


namespace App\Twig;

use App\Service\NavbarNotificationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private NavbarNotificationService $notifier;

    public function __construct(NavbarNotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

 public function getFunctions(): array
 {
  return [
   new TwigFunction('unread_messages_count', [$this->notifier, 'getUnreadMessagesCount']),
new TwigFunction('unread_likes_count', [$this, 'getUnreadLikesCount']),
  ];
 }

public function getUnreadLikesCount(): int
    {
        return $this->notifier->getUnreadLikesCount();
    }
}
