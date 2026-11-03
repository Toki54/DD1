<?php

namespace App\Controller;

use App\Entity\PromotionCampaign;
use App\Repository\PromotionCampaignRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        UserRepository $userRepository,
        PromotionCampaignRepository $promotionCampaignRepository,
    ): Response
    {
        $campaign = $promotionCampaignRepository->find(PromotionCampaign::MEN_PREMIUM);
        $registeredMen = $userRepository->countMenRegisteredAfterId($campaign?->getStartUserId() ?? 0);

        return $this->render('home/home.html.twig', [
            'lifetimeRemaining' => max(0, 5 - $registeredMen),
            'sixMonthsRemaining' => max(0, min(5, 10 - $registeredMen)),
            'oneMonthRemaining' => max(0, min(40, 50 - $registeredMen)),
        ]);
    }
}
