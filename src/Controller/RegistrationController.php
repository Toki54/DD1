<?php

namespace App\Controller;

use App\Entity\User;
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
 public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
 {
  $user = new User();
  $form = $this->createForm(RegistrationFormType::class, $user);

  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   // Hash du mot de passe
   $user->setPassword(
    $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
   );

   // Attribution du rôle par défaut
   $user->setRoles(['ROLE_USER']);

   // Enregistrer l'utilisateur en base de données
   $entityManager->persist($user);
   $entityManager->flush();

   // Rediriger vers la page d'accueil après l'inscription
   return $this->redirectToRoute('app_home');
  }

  return $this->render('registration/register.html.twig', [
   'registrationForm' => $form->createView(),
  ]);
 }
}
