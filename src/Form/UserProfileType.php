<?php

namespace App\Form;

use App\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserProfileType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options): void
 {
  $builder

   ->add('sex', ChoiceType::class, [
    'label'    => 'Sex',
    'choices'  => [
     'Male'   => 'male',
     'Female' => 'female',
     'Other'  => 'other',
    ],
    'expanded' => false,
    'multiple' => false,
   ])
   ->add('situation', ChoiceType::class, [
    'label'    => 'Situation',
    'choices'  => [
     'En couple'   => 'En couple',
     'Celibataire' => 'Celibataire',
     'Je le garde pour moi'  => 'Je le garde pour moi',
    ],
    'required' => false,
   ])
   ->add('biography', TextareaType::class, [
    'label'    => 'A propos de moi',
    'required' => false,
   ])
   ->add('research', ChoiceType::class, [
    'label'    => 'Je recherche',
    'choices'  => [
     'Homme'   => 'Homme',
     'Femme' => 'Femme',
     'Couple' => 'Couple',
    ],
    'required' => false,
   ])
   ->add('avatarFile', FileType::class, [
        'label' => 'Profile Picture',
        'mapped' => false, // Ne mappe pas directement sur l’entité (car on stocke le chemin dans `avatar`)
        'required' => false,
    ])
    ->add('photoFiles', FileType::class, [
        'label' => 'Upload Photos',
        'mapped' => false, // Stockage indirect via `photos`
        'multiple' => true,
        'required' => false,
    ]);
 }

 public function configureOptions(OptionsResolver $resolver): void
 {
  $resolver->setDefaults([
   'data_class' => UserProfile::class,
  ]);
 }
}
