<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $totalUsers = (int) $entityManager->getRepository(User::class)->count([]);

        // 100 premières inscriptions => offre "premium à vie"
        $premiumRemaining = max(0, 100 - $totalUsers);

        return $this->render('home/home.html.twig', [
            'controller_name'   => 'HomeController',
            'premiumRemaining'  => $premiumRemaining,
        ]);
    }
}
