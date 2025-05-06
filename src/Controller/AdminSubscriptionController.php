<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use App\Form\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/subscriptions')]
class AdminSubscriptionController extends AbstractController
{
 #[Route('/', name: 'admin_subscriptions_index')]
 public function index(EntityManagerInterface $entityManager): Response
 {
  $this->denyAccessUnlessGranted('ROLE_ADMIN');

  $subscriptions = $entityManager->getRepository(Subscription::class)->findAll();

  return $this->render('admin/subscription/index.html.twig', [
   'subscriptions' => $subscriptions,
  ]);
 }

 #[Route('/create/{userId}', name: 'admin_subscriptions_create')]
 public function create(int $userId, Request $request, EntityManagerInterface $entityManager): Response
 {
  $this->denyAccessUnlessGranted('ROLE_ADMIN');

  $user = $entityManager->getRepository(User::class)->find($userId);

  if (!$user) {
   throw $this->createNotFoundException('Utilisateur non trouvé.');
  }

  $subscription = new Subscription();
  $subscription->setUser($user);
  $subscription->setStartDate(new \DateTime());
  $subscription->setEndDate((new \DateTime())->modify('+1 month'));
  $subscription->setPlan('basic');

  $form = $this->createForm(SubscriptionType::class, $subscription);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   $entityManager->persist($subscription);
   $entityManager->flush();

   $this->addFlash('success', 'Abonnement créé avec succès.');
   return $this->redirectToRoute('admin_subscriptions_index');
  }

  return $this->render('admin/subscription/create.html.twig', [
   'form' => $form->createView(),
   'user' => $user,
  ]);
 }

 #[Route('/admin/subscription/{id}/edit', name: 'admin_subscriptions_edit')]
public function edit(Request $request, Subscription $subscription, EntityManagerInterface $em): Response
{
    $form = $this->createForm(SubscriptionType::class, $subscription);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Abonnement modifié avec succès.');
        return $this->redirectToRoute('admin_subscriptions_index');
    }

    return $this->render('admin/subscription/edit.html.twig', [
        'form' => $form->createView(),
        'subscription' => $subscription,
    ]);
}

 #[Route('/delete/{id}', name: 'admin_subscriptions_delete')]
 public function delete(int $id, EntityManagerInterface $entityManager): Response
 {
  $this->denyAccessUnlessGranted('ROLE_ADMIN');

  $subscription = $entityManager->getRepository(Subscription::class)->find($id);

  if (!$subscription) {
   throw $this->createNotFoundException('Abonnement non trouvé.');
  }

  $entityManager->remove($subscription);
  $entityManager->flush();

  $this->addFlash('success', 'Abonnement supprimé avec succès.');
  return $this->redirectToRoute('admin_subscriptions_index');
 }
}
