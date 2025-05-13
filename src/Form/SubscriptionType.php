<?php

namespace App\Form;

use App\Entity\Subscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
   '1 semaine - 5 €' => ['duration' => '+1 week', 'price' => 5.00],
   '1 mois - 15 €'   => ['duration' => '+1 month', 'price' => 15.00],
   '3 mois - 30 €'   => ['duration' => '+3 months', 'price' => 30.00],
   '6 mois - 50 €'   => ['duration' => '+6 months', 'price' => 50.00],
   '1 an - 80 €'     => ['duration' => '+1 year', 'price' => 80.00],
  ];

  $builder
   ->add('plan', ChoiceType::class, [
    'choices' => array_combine(array_keys($plans), array_keys($plans)),
    'label'   => 'Choisissez votre abonnement',
   ])
   ->add('price', HiddenType::class)
   ->add('startDate', HiddenType::class)
   ->add('endDate', HiddenType::class);

  $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($plans) {
   $data = $event->getData();
   if (isset($data['plan'], $plans[$data['plan']])) {
    $startDate = new \DateTime();
    $endDate   = (clone $startDate)->modify($plans[$data['plan']]['duration']);

    $data['price']     = $plans[$data['plan']]['price'];
    $data['startDate'] = $startDate; // objet DateTime
    $data['endDate']   = $endDate; // objet DateTime

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
