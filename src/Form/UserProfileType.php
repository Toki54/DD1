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

class UserProfileType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options): void
 {
  $departments = [];
  for ($i = 1; $i <= 95; $i++) {
   $departments[str_pad($i, 2, '0', STR_PAD_LEFT)] = str_pad($i, 2, '0', STR_PAD_LEFT);
  }

  $builder
   ->add('sex', ChoiceType::class, [
    'label'   => 'Sexe',
    'choices' => ['Homme' => 'Homme', 'Femme' => 'Femme', 'Autre' => 'Autre'],
   ])
   ->add('situation', ChoiceType::class, [
    'label'   => 'Situation',
    'choices' => ['En couple' => 'En couple', 'Célibataire' => 'Célibataire'],
   ])
   ->add('research', ChoiceType::class, [
    'label'    => 'Je recherche',
    'choices'  => [
     'Homme'  => 'Homme',
     'Femme'  => 'Femme',
     'Couple' => 'Couple',
    ],
    'required' => false,
   ])
   ->add('biography', TextareaType::class, ['label' => 'À propos de moi'])
   ->add('department', ChoiceType::class, [
    'label'   => 'Département',
    'choices' => $departments,
   ])
   ->add('city', TextType::class, [
    'label'    => 'Ville',
    'attr'     => ['class' => 'autocomplete-city'],
    'required' => false,
   ])
   ->add('avatarFile', FileType::class, ['label' => 'Photo de Profil', 'mapped' => false, 'required' => false])
   ->add('photoFiles', FileType::class, ['label' => 'Photos', 'mapped' => false, 'multiple' => true, 'required' => false]);
 }

 public function configureOptions(OptionsResolver $resolver): void
 {
  $resolver->setDefaults(['data_class' => UserProfile::class]);
 }
}
