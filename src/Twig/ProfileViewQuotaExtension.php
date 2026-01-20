<?php

namespace App\Twig;

use App\Entity\User;
use App\Services\ProfileViewQuotaService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ProfileViewQuotaExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ProfileViewQuotaService $profileViewQuotaService,
    ) {}

    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return ['profileViewQuota' => null];
        }

        if ($this->security->isGranted('ROLE_PREMIUM')) {
            return [
                'profileViewQuota' => [
                    'isLimited' => false,
                    'limit' => null,
                    'usedToday' => null,
                    'remaining' => null,
                ],
            ];
        }

        $profile = $user->getProfile();
        if (!$profile) {
            return ['profileViewQuota' => null];
        }

        $limit = $this->profileViewQuotaService->getDailyLimit();
        $used  = $this->profileViewQuotaService->countDistinctProfilesViewedToday($profile);

        return [
            'profileViewQuota' => [
                'isLimited' => true,
                'limit' => $limit,
                'usedToday' => $used,
                'remaining' => max(0, $limit - $used),
            ],
        ];
    }
}
