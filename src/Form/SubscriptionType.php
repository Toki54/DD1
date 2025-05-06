<?php

namespace App\Form;

use App\Entity\Subscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options): void
 {
  $prices = [
   'basic'   => 10.00,
   'premium' => 20.00,
   'vip'     => 30.00,
  ];

  $builder
   ->add('plan', ChoiceType::class, [
    'choices' => [
     'Basic - 10 €'   => 'basic',
     'Premium - 20 €' => 'premium',
     'VIP - 30 €'     => 'vip',
    ],
    'label'   => 'Plan d\'abonnement',
   ])
   ->add('startDate', DateType::class, [
    'widget' => 'single_text',
    'label'  => 'Date de début',
    'data'   => new \DateTime(),
   ])
   ->add('endDate', DateType::class, [
    'widget' => 'single_text',
    'label'  => 'Date de fin',
    'data'   => (new \DateTime())->modify('+1 month'),
   ]);

  // Met automatiquement le prix selon le plan
  $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($prices) {
   $data = $event->getData();
   if (isset($data['plan']) && isset($prices[$data['plan']])) {
    $data['price'] = $prices[$data['plan']];
    $event->setData($data);
   }
  });
 }

 public function configureOptions(OptionsResolver $resolver): void
 {
  $resolver->setDefaults([
   'data_class' => Subscription::class,
  ]);
 }
}
