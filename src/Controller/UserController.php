<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeletedMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/user')]
final class UserController extends AbstractController
{
 #[Route(name: 'app_user_index', methods: ['GET'])]
 #[IsGranted('ROLE_ADMIN')]
 public function index(UserRepository $userRepository): Response
 {
  return $this->render('user/index.html.twig', [
   'users' => $userRepository->findAll(),
  ]);
 }

 #[Route('/deleted-conversations', name: 'app_deleted_conversations', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
public function deletedConversations(
    Request $request,
    UserRepository $userRepository,
    MessageRepository $messageRepository,
    DeletedMessageRepository $deletedMessageRepository
): Response {
    $participantIds = array_values(array_unique(array_merge(
        $messageRepository->findParticipantIds(),
        $deletedMessageRepository->findParticipantIds()
    )));

    $users = $participantIds === [] ? [] : $userRepository->findBy(['id' => $participantIds]);
    usort($users, static fn (User $left, User $right): int => strcasecmp(
        (string) $left->getPseudo(),
        (string) $right->getPseudo()
    ));

    $usersById = [];
    foreach ($users as $user) {
        if ($user->getId() !== null) {
            $usersById[$user->getId()] = $user;
        }
    }

    $selectedUser = null;
    $selectedUserId = $request->query->getInt('user');
    if ($selectedUserId > 0) {
        $selectedUser = $usersById[$selectedUserId] ?? null;
        if ($selectedUser === null) {
            throw $this->createNotFoundException('Utilisateur sans conversation introuvable.');
        }
    }

    $activeMessages = $selectedUser ? $messageRepository->findActiveMessagesForUser($selectedUser) : [];
    $deletedMessages = $selectedUser ? $deletedMessageRepository->findBySenderOrReceiver($selectedUser) : [];

    return $this->render('user/deletedMess.html.twig', [
        'users' => $users,
        'selectedUser' => $selectedUser,
        'activeMessages' => $activeMessages,
        'deletedMessages' => $deletedMessages,
    ]);
}

 #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
 #[IsGranted('ROLE_ADMIN')]
 public function new (Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
 {
  $user = new User();
  $form = $this->createForm(UserType::class, $user, ['require_password' => true]);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid() && $this->applyPremiumSubscription($user, $form, $entityManager)) {
   $user->setPassword($passwordHasher->hashPassword($user, (string) $form->get('plainPassword')->getData()));
   $entityManager->persist($user);
   $entityManager->flush();

   return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
  }

  return $this->render('user/new.html.twig', [
   'user' => $user,
   'form' => $form,
  ]);
 }

 #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
 #[IsGranted('ROLE_ADMIN')]
 public function show(User $user): Response
 {
  return $this->render('user/show.html.twig', [
   'user' => $user,
  ]);
 }

 #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
 #[IsGranted('ROLE_ADMIN')]
 public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
 {
  $form = $this->createForm(UserType::class, $user);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid() && $this->applyPremiumSubscription($user, $form, $entityManager)) {
   $entityManager->flush();

   return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
  }

  return $this->render('user/edit.html.twig', [
   'user' => $user,
   'form' => $form,
  ]);
 }

 #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
 #[IsGranted('ROLE_ADMIN')]
 public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
 {
  if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
   $entityManager->remove($user);
   $entityManager->flush();
  }

  return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
 }

 private function applyPremiumSubscription(User $user, FormInterface $form, EntityManagerInterface $entityManager): bool
 {
  /** @var list<string> $roles */
  $roles = $form->get('roles')->getData() ?? [];
  $hasPremiumRole = in_array('ROLE_PREMIUM', $roles, true);
  $subscription = $user->getSubscription();

  if (!$hasPremiumRole) {
   if ($subscription !== null) {
    $subscription->setActive(false);
   }

   return true;
  }

  $duration = (string) ($form->get('premiumDuration')->getData() ?? '');
  $now = new \DateTimeImmutable();

  if ($duration === '') {
   if ($subscription !== null
    && $subscription->isActive()
    && $subscription->getEndDate() !== null
    && $subscription->getEndDate() > $now) {
    return true;
   }

   $form->get('premiumDuration')->addError(new FormError('Choisissez la durée de l’abonnement Premium.'));
   return false;
  }

  $durations = [
   'week' => ['label' => '1 semaine', 'modifier' => '+1 week', 'plan' => '1 semaine - 5 €'],
   'month' => ['label' => '1 mois', 'modifier' => '+1 month', 'plan' => '1 mois - 15 €'],
   'three_months' => ['label' => '3 mois', 'modifier' => '+3 months', 'plan' => '3 mois - 30 €'],
   'six_months' => ['label' => '6 mois', 'modifier' => '+6 months', 'plan' => '6 mois - 50 €'],
   'year' => ['label' => '1 an', 'modifier' => '+1 year', 'plan' => '1 an - 80 €'],
   'lifetime' => ['label' => 'À vie', 'modifier' => null, 'plan' => 'À vie - 100 €'],
  ];

  if (!isset($durations[$duration])) {
   $form->get('premiumDuration')->addError(new FormError('Durée d’abonnement invalide.'));
   return false;
  }

  if ($subscription === null) {
   $subscription = new Subscription();
   $user->setSubscription($subscription);
  }

  $endDate = $duration === 'lifetime'
   ? new \DateTimeImmutable('9999-12-31 23:59:59')
   : $now->modify($durations[$duration]['modifier']);

  $subscription
   ->setStartDate($now)
   ->setEndDate($endDate)
   ->setPlan($durations[$duration]['plan'])
   ->setPrice(0.0)
   ->setActive(true)
   ->setStripeSubscriptionId(null)
   ->setStripeSessionId(null);

  $entityManager->persist($subscription);

  return true;
 }
}
