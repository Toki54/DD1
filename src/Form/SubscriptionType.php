<?php

namespace App\Form;

use App\Entity\Subscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options): void
 {
  $builder
   ->add('plan', ChoiceType::class, [
    'choices' => [
     'Basic'   => 'basic',
     'Premium' => 'premium',
     'VIP'     => 'vip',
    ],
    'label'   => 'Plan d\'abonnement',
   ])
   ->add('startDate', DateType::class, [
    'widget' => 'single_text',
    'label'  => 'Date de début',
    'data'   => new \DateTime(), // Par défaut, la date du jour
   ])
   ->add('endDate', DateType::class, [
    'widget' => 'single_text',
    'label'  => 'Date de fin',
    'data'   => (new \DateTime())->modify('+1 month'), // Par défaut, 1 mois après la date de début
   ])
   ->add('submit', SubmitType::class, [
    'label' => 'Souscrire',
   ]);
 }

 public function configureOptions(OptionsResolver $resolver): void
 {
  $resolver->setDefaults([
   'data_class' => Subscription::class,
  ]);
 }
}
