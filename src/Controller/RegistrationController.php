<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\UserProfile;
use App\Entity\PromotionCampaign;
use App\Form\RegistrationFormType;
use App\Repository\PromotionCampaignRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient,
        UserRepository $userRepository,
        PromotionCampaignRepository $promotionCampaignRepository,
        #[Autowire(service: 'limiter.registration')] RateLimiterFactory $registrationLimiter
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $limit = $registrationLimiter->create((string) ($request->getClientIp() ?? 'unknown'))->consume();
            if (!$limit->isAccepted()) {
                $this->addFlash('error', 'Trop de tentatives d’inscription. Réessayez dans quelques instants.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
                ]);
            }

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

            if ($sex === 'Femme') {
                $subscription = new Subscription();
                $subscription->setUser($user);
                $subscription->setPlan('Premium Femme');
                $subscription->setPrice(0);
                $subscription->setStartDate(new \DateTimeImmutable());
                $subscription->setEndDate(new \DateTimeImmutable('9999-12-31 23:59:59'));
                $subscription->setActive(true);
                $user->setSubscription($subscription);
            } elseif ($sex === 'Homme') {
                $campaign = $promotionCampaignRepository->find(PromotionCampaign::MEN_PREMIUM);
                $startUserId = $campaign?->getStartUserId() ?? 0;
                $registrationRank = $userRepository->countMenRegisteredAfterId($startUserId) + 1;

                if ($registrationRank <= 50) {
                    $duration = $registrationRank <= 5 ? '+1 year' : '+6 months';
                    $now = new \DateTimeImmutable();

                    $subscription = new Subscription();
                    $subscription->setUser($user);
                    $subscription->setPlan($registrationRank <= 5 ? 'Offre lancement Homme - 1 an' : 'Offre lancement Homme - 6 mois');
                    $subscription->setPrice(0);
                    $subscription->setStartDate($now);
                    $subscription->setEndDate($now->modify($duration));
                    $subscription->setActive(true);
                    $user->setSubscription($subscription);
                    $user->setRoles(['ROLE_USER', 'ROLE_PREMIUM']);
                }
            }

            $entityManager->persist($user);

            $entityManager->flush();

            

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
        ]);
    }
}
