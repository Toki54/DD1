<?php

namespace App\Form;

use App\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options): void
 {
  $builder
   ->add('sex')
   ->add('situation')
   ->add('research')
   ->add('biography')
   ->add('department')
   ->add('city')
   ->add('avatarFile', FileType::class, [
    'label'    => 'Avatar (JPEG, PNG, GIF)',
    'required' => false,
    'mapped'   => false,
   ])
   ->add('photoFiles', FileType::class, [
    'label'    => 'Ajouter des photos',
    'required' => false,
    'multiple' => true,
    'mapped'   => false,
   ]);
 }

 public function configureOptions(OptionsResolver $resolver): void
 {
  $resolver->setDefaults([
   'data_class' => UserProfile::class,
  ]);
 }
}
