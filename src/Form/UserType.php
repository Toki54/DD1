<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => ['Administrateur' => 'ROLE_ADMIN', 'Premium' => 'ROLE_PREMIUM'],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('premiumDuration', ChoiceType::class, [
                'label' => 'Durée de l’abonnement Premium',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Choisir une durée',
                'choices' => [
                    '1 semaine' => 'week',
                    '1 mois' => 'month',
                    '3 mois' => 'three_months',
                    '6 mois' => 'six_months',
                    '1 an' => 'year',
                    'À vie' => 'lifetime',
                ],
            ])
            ->add('pseudo')
        ;

        if ($options['require_password']) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => false,
        ]);
    }
}
