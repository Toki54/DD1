<?php

namespace App\Form;

use App\Entity\Subscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $plans = [
            '1 semaine - 5 €' => ['duration' => '+1 week',   'price' => 5.00],
            '1 mois - 15 €'   => ['duration' => '+1 month',  'price' => 15.00],
            '3 mois - 30 €'   => ['duration' => '+3 months', 'price' => 30.00],
            '6 mois - 50 €'   => ['duration' => '+6 months', 'price' => 50.00],
            '1 an - 80 €'     => ['duration' => '+1 year',   'price' => 80.00],

            // ✅ NOUVEAU : Abonnement à vie
            'À vie - 100 €'   => ['duration' => 'LIFETIME',  'price' => 100.00],
        ];

        $builder
            ->add('plan', HiddenType::class)
            ->add('price', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('startDate', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('endDate', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($plans) {
            $data = $event->getData();
            if (!is_array($data) || empty($data['plan'])) {
                return;
            }

            $plan = $data['plan'];
            if (!isset($plans[$plan])) {
                return;
            }

            /** @var Subscription $subscription */
            $subscription = $event->getForm()->getData();

            $startDate = new \DateTime();

            if ($plans[$plan]['duration'] === 'LIFETIME') {
                $endDate = new \DateTime('9999-12-31 23:59:59');
            } else {
                $endDate = (clone $startDate)->modify($plans[$plan]['duration']);
            }

            $subscription->setPlan($plan);
            $subscription->setPrice((float) $plans[$plan]['price']);
            $subscription->setStartDate($startDate);
            $subscription->setEndDate($endDate);
            $subscription->setActive(true);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
        ]);
    }
}
