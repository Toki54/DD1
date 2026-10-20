<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_dashboard')]
#[IsGranted('ROLE_ADMIN')]
final class AdminDashboardController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $count = fn (string $class): int => (int) $entityManager->getRepository($class)
            ->createQueryBuilder('e')->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $count(User::class),
            'subscriptionCount' => $count(Subscription::class),
            'activeSubscriptionCount' => (int) $entityManager->getRepository(Subscription::class)
                ->createQueryBuilder('s')->select('COUNT(s.id)')->where('s.active = true')->getQuery()->getSingleScalarResult(),
            'messageCount' => $count(Message::class),
        ]);
    }
}
