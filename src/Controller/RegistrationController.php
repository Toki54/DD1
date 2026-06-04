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

            /* ===== ROLE PAR DÉFAUT ===== */
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush(); // IMPORTANT : ID requis

            

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
        ]);
    }
}
