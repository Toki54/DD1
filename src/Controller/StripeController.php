<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Subscription;
use Stripe\Checkout\Session;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
 private UrlGeneratorInterface $urlGenerator;
 private EntityManagerInterface $em;

 public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
 {
  $this->urlGenerator = $urlGenerator;
  $this->em           = $em;
 }

 #[Route('/create-checkout-session', name: 'app_stripe_checkout', methods: ['POST'])]
 public function checkout(Request $request, Security $security): JsonResponse
 {
  $user = $security->getUser();
  if (!$user instanceof \App\Entity\User) {
   return new JsonResponse(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
  }

  $data   = json_decode($request->getContent(), true);
  $plan   = $data['plan'] ?? '';
  $amount = $data['amount'] ?? 0;

  $priceInCents = intval($amount * 100);

  Stripe::setApiKey($this->getParameter('stripe_secret_key'));

  $session = Session::create([
   'payment_method_types' => ['card'],
   'customer_email'       => $user->getEmail(),
   'line_items'           => [[
    'price_data' => [
     'currency'     => 'eur',
     'product_data' => ['name' => $plan],
     'unit_amount'  => $priceInCents,
    ],
    'quantity'   => 1,
   ]],
   'mode'                 => 'payment',
   'success_url'          => $this->urlGenerator->generate('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
   'cancel_url'           => $this->urlGenerator->generate('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
   'metadata'             => [
    'user_id' => $user->getId(),
    'plan'    => $plan,
    'price'   => $amount,
   ],
  ]);

  $subscription = new Subscription();
  $subscription->setUser($user);
  $subscription->setPlan($plan);
  $subscription->setPrice((float) $amount);
  $subscription->setStartDate(new \DateTime());
  $subscription->setEndDate((new \DateTime())->modify('+1 month'));
  $subscription->setActive(false);
  $subscription->setStripeSessionId($session->id);

  $this->em->persist($subscription);
  $this->em->flush();

  return new JsonResponse(['id' => $session->id]);
 }

 #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
 public function webhook(
  Request $request,
  SubscriptionRepository $subscriptionRepo,
  UserRepository $userRepo
 ): Response {
  $payload   = $request->getContent();
  $sigHeader = $request->headers->get('stripe-signature');
  $secret    = $this->getParameter('stripe_webhook_secret');

  try {
   $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
  } catch (\UnexpectedValueException $e) {
   return new Response('Invalid payload', 400);
  } catch (\Stripe\Exception\SignatureVerificationException $e) {
   return new Response('Invalid signature', 400);
  }

  if ($event->type === 'checkout.session.completed') {
   $session = $event->data->object;

   $stripeSessionId = $session->id;
   $subscription    = $subscriptionRepo->findOneBy(['stripeSessionId' => $stripeSessionId]);

   if ($subscription) {
    $subscription->setActive(true);
    $this->em->persist($subscription);

    $user = $subscription->getUser();
    if ($user) {
     $roles = $user->getRoles();
     if (!in_array('ROLE_PREMIUM', $roles, true)) {
      $roles[] = 'ROLE_PREMIUM';
      $user->setRoles($roles);
      $this->em->persist($user);
     }
    }

    $this->em->flush();
   }
  }

  return new Response('Webhook reçu', 200);
 }

 #[Route('/payment-success', name: 'app_stripe_success')]
 public function success(): Response
 {
  return $this->render('stripe/success.html.twig');
 }

 #[Route('/payment-cancel', name: 'app_stripe_cancel')]
 public function cancel(): Response
 {
  return $this->render('stripe/cancel.html.twig');
 }

 #[Route('/subscribes', name: 'app_stripe_subscribe')]
 public function subscribe(): Response
 {
  return $this->render('stripe/subscribe.html.twig', [
   'stripe_public_key' => $this->getParameter('stripe_public_key'),
  ]);
 }
}
