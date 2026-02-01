<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        ParameterBagInterface $params
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));

            /** @var User|null $user */
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));

                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
                $em->flush();

                $appUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost:8000'), '/');
                $resetLink = $appUrl . $this->generateUrl('app_reset_password', ['token' => $token]);

                $fromEmail = (string) $params->get('mailer_from');

                $emailMessage = (new Email())
                    ->from($fromEmail)
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html(
                        '<p>Bonjour,</p>
                        <p>Cliquez sur le lien suivant pour réinitialiser votre mot de passe :</p>
                        <p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES) . '">Réinitialiser</a></p>
                        <p>Ce lien expire dans 1 heure.</p>'
                    );

                $mailer->send($emailMessage);
            }

            $this->addFlash('success', 'Si un compte existe, un email a été envoyé.');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User|null $user */
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        $now = new \DateTimeImmutable();

        if (
            !$user ||
            !$user->getResetTokenExpiresAt() ||
            $user->getResetTokenExpiresAt() < $now
        ) {
            $this->addFlash('error', 'Lien invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');

            if (mb_strlen($password) < 8) {
                $this->addFlash('error', 'Le mot de passe doit faire au moins 8 caractères.');
                return $this->render('security/reset_password.html.twig');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $password);

            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig');
    }
}
