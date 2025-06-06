<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
 private StripeClient $stripe;

 public function __construct(string $stripeSecretKey)
 {
  $this->stripe = new StripeClient($stripeSecretKey);
 }

 public function getClient(): StripeClient
 {
  return $this->stripe;
 }
}
