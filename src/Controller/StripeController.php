<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
    }

    #[Route('/subscribes', name: 'app_stripe_subscribe', methods: ['GET'])]
    public function subscribe(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('stripe/subscribe.html.twig', [
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    #[Route('/create-checkout-session', name: 'app_stripe_checkout', methods: ['POST'])]
    public function checkout(
        Request $request,
        Security $security,
        SubscriptionRepository $subscriptionRepo
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $plan = (string) ($data['plan'] ?? '');
        $amount = (float) ($data['amount'] ?? 0);

        if ($plan === '' || $amount <= 0) {
            return new JsonResponse(['error' => 'Plan ou montant invalide'], Response::HTTP_BAD_REQUEST);
        }

        $plans = [
            '1 semaine - 5 €' => ['duration' => '+1 week',   'price' => 5.00],
            '1 mois - 15 €'   => ['duration' => '+1 month',  'price' => 15.00],
            '3 mois - 30 €'   => ['duration' => '+3 months', 'price' => 30.00],
            '6 mois - 50 €'   => ['duration' => '+6 months', 'price' => 50.00],
            '1 an - 80 €'     => ['duration' => '+1 year',   'price' => 80.00],
            'À vie - 100 €'   => ['duration' => 'LIFETIME',  'price' => 100.00],
        ];

        if (!isset($plans[$plan])) {
            return new JsonResponse(['error' => 'Plan inconnu'], Response::HTTP_BAD_REQUEST);
        }

        $serverPrice = (float) $plans[$plan]['price'];
        $duration    = (string) $plans[$plan]['duration'];
        $priceInCents = (int) round($serverPrice * 100);

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        try {
            $session = Session::create([
                // ✅ Compatible avec anciennes versions stripe-php
                'payment_method_types' => ['card'],

                'customer_email' => $user->getEmail(),

                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => $plan],
                        'unit_amount' => $priceInCents,
                    ],
                    'quantity' => 1,
                ]],

                'mode' => 'payment',

                'success_url' => $this->urlGenerator->generate('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url'  => $this->urlGenerator->generate('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),

                'metadata' => [
                    'user_id' => (string) $user->getId(),
                    'plan'    => $plan,
                    'price'   => (string) $serverPrice,
                ],
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur Stripe: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $startDate = new \DateTime();

        // endDate NOT NULL dans ton entity => date très loin pour "à vie"
        if ($duration === 'LIFETIME') {
            $endDate = new \DateTime('9999-12-31 23:59:59');
        } else {
            $endDate = (clone $startDate)->modify($duration);
        }

        // ✅ IMPORTANT : OneToOne => on UPDATE si existe déjà, sinon on CREATE
        $subscription = $subscriptionRepo->findOneBy(['user' => $user]);
        $isNew = false;

        if (!$subscription) {
            $subscription = new Subscription();
            $subscription->setUser($user);
            $isNew = true;
        }

        $subscription->setPlan($plan);
        $subscription->setPrice($serverPrice);
        $subscription->setStartDate($startDate);
        $subscription->setEndDate($endDate);
        $subscription->setActive(false);
        $subscription->setStripeSessionId($session->id);

        try {
            if ($isNew) {
                $this->em->persist($subscription);
            }
            $this->em->flush();
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur DB: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

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

            $subscription = $subscriptionRepo->findOneBy(['stripeSessionId' => $session->id]);
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

    #[Route('/payment-success', name: 'app_stripe_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('stripe/success.html.twig');
    }

    #[Route('/payment-cancel', name: 'app_stripe_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
}
