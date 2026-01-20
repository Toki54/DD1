<?php

namespace App\Twig;

use App\Entity\User;
use App\Services\MessageQuotaService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class MessageQuotaExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly MessageQuotaService $messageQuotaService,
    ) {}

    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return ['messageQuota' => null];
        }

        if ($this->security->isGranted('ROLE_PREMIUM')) {
            return [
                'messageQuota' => [
                    'isLimited' => false,
                    'limit' => null,
                    'sentToday' => null,
                    'remaining' => null,
                ],
            ];
        }

        $limit = $this->messageQuotaService->getDailyLimitFor($user);
        $sent  = $this->messageQuotaService->countSentToday($user);

        return [
            'messageQuota' => [
                'isLimited' => true,
                'limit' => $limit,
                'sentToday' => $sent,
                'remaining' => max(0, $limit - $sent),
            ],
        ];
    }
}
