<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /* ===== HASH PASSWORD ===== */
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            /* ===== ROLE PAR DÉFAUT ===== */
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush(); // IMPORTANT : ID requis

            /* ===== OFFRE 100 PREMIERS ===== */
            $totalUsers = $entityManager
                ->getRepository(User::class)
                ->count([]);

            if ($totalUsers <= 100) {

                // Rôle premium à vie
                $roles = $user->getRoles();
                if (!in_array('ROLE_PREMIUM', $roles, true)) {
                    $roles[] = 'ROLE_PREMIUM';
                    $user->setRoles($roles);
                }

                // Abonnement à vie
                $subscription = new Subscription();
                $subscription->setUser($user);
                $subscription->setPlan('Premium à vie');
                $subscription->setPrice(0.0);
                $subscription->setStartDate(new \DateTime());
                $subscription->setEndDate(null); // À VIE
                $subscription->setActive(true);

                $entityManager->persist($subscription);
                $entityManager->persist($user);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
