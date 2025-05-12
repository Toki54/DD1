<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
 #[Route('/profile/subscribe', name: 'app_subscription')]
 public function subscribe(Request $request, EntityManagerInterface $entityManager, Security $security): Response
 {
  // Vérifie que l'utilisateur est connecté
  $user = $security->getUser();
  if (!$user) {
   $this->addFlash('error', 'Vous devez être connecté pour vous abonner.');
   return $this->redirectToRoute('app_login');
  }

  // Création d'une nouvelle instance d'abonnement et affectation de l'utilisateur connecté
  $subscription = new Subscription();
  $subscription->setUser($user);



  // Création du formulaire (celui que vous avez déjà configuré)
  $form = $this->createForm(SubscriptionType::class, $subscription);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   // Persist et flush en base de données
   $entityManager->persist($subscription);
   $entityManager->flush();

   $this->addFlash('success', 'Abonnement effectué avec succès !');
   return $this->redirectToRoute('app_profile_show');
  }

  return $this->render('profile/subscribe.html.twig', [
   'form' => $form->createView(),
  ]);
 }
}
