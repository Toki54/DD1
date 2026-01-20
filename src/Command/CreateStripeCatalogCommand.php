<?php

namespace App\Command;

use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:create-stripe-catalog',
    description: 'Crée les produits et prices Stripe (paiement unique)'
)]
class CreateStripeCatalogCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // ✅ 1 PRODUIT
        $product = Product::create([
            'name' => 'Parlotte Premium',
            'description' => 'Accès Premium (galerie, messages, etc.)',
        ]);

        // ✅ PRICES (centimes)
        $prices = [
            '1 semaine - 5 €' => 500,
            '1 mois - 15 €'   => 1500,
            '3 mois - 30 €'   => 3000,
            '6 mois - 50 €'   => 5000,
            '1 an - 80 €'     => 8000,
            'À vie - 100 €'   => 10000,
        ];

        $output->writeln('');
        $output->writeln('✅ Produit créé : ' . $product->id);
        $output->writeln('');

        foreach ($prices as $label => $amount) {
            $price = Price::create([
                'product' => $product->id,
                'currency' => 'eur',
                'unit_amount' => $amount,
                'nickname' => $label,
                'metadata' => [
                    'plan' => $label,
                ],
            ]);

            $envKey = match ($label) {
                '1 semaine - 5 €' => 'STRIPE_PRICE_WEEK',
                '1 mois - 15 €'   => 'STRIPE_PRICE_MONTH',
                '3 mois - 30 €'   => 'STRIPE_PRICE_3MONTHS',
                '6 mois - 50 €'   => 'STRIPE_PRICE_6MONTHS',
                '1 an - 80 €'     => 'STRIPE_PRICE_YEAR',
                'À vie - 100 €'   => 'STRIPE_PRICE_LIFETIME',
            };

            $output->writeln($envKey . '=' . $price->id);
        }

        $output->writeln('');
        $output->writeln('➡️ Copie ces lignes dans .env.local');
        $output->writeln('');

        return Command::SUCCESS;
    }
}
