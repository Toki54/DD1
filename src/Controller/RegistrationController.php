<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\UserProfile;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ===== CAPTCHA (reCAPTCHA v2 checkbox) =====
            $recaptchaResponse = (string) $request->request->get('g-recaptcha-response', '');
            if ($recaptchaResponse === '') {
                $this->addFlash('error', 'Veuillez valider le captcha.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
                ]);
            }

            $recaptchaSecret = (string) $this->getParameter('recaptcha_secret_key');

            try {
                $verifyResponse = $httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                    'body' => [
                        'secret' => $recaptchaSecret,
                        'response' => $recaptchaResponse,
                        'remoteip' => (string) $request->getClientIp(),
                    ],
                ]);

                $verifyData = $verifyResponse->toArray(false);
                $success = (bool) ($verifyData['success'] ?? false);

                if (!$success) {
                    $this->addFlash('error', 'Captcha invalide. Réessaie.');
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form->createView(),
                        'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
                    ]);
                }
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Impossible de vérifier le captcha. Réessaie.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
                ]);
            }

            /* ===== HASH PASSWORD ===== */
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $sex = $form->get('sex')->getData();

            $profile = new UserProfile();
            $profile->setSex($sex);
            $user->setProfile($profile);

            /* ===== RÔLE DÉFINI À L'INSCRIPTION ===== */
            $user->setRoles($sex === 'Femme' ? ['ROLE_USER', 'ROLE_PREMIUM'] : ['ROLE_USER']);

            $entityManager->persist($user);

            if ($sex === 'Femme') {
                $subscription = new Subscription();
                $subscription->setUser($user);
                $subscription->setPlan('Premium Femme');
                $subscription->setPrice(0);
                $subscription->setStartDate(new \DateTimeImmutable());
                $subscription->setEndDate(new \DateTimeImmutable('9999-12-31 23:59:59'));
                $subscription->setActive(true);
                $user->setSubscription($subscription);
            }

            $entityManager->flush();

            

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
        ]);
    }
}
