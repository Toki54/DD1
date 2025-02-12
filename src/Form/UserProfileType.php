<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class UserProfileType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options)
 {
  $builder
   ->add('sex', ChoiceType::class, [
    'choices' => [
     'Male'   => 'male',
     'Female' => 'female',
     'Other'  => 'other',
    ],
   ])
   ->add('situation', TextType::class, ['required' => false])
   ->add('research', TextType::class, ['required' => false])
   ->add('biography', TextareaType::class, ['required' => false])
   ->add('avatar', FileType::class, [
    'label'       => 'Avatar (Image file)',
    'mapped'      => false,
    'required'    => false,
    'constraints' => [
     new File([
      'maxSize'          => '5M',
      'mimeTypes'        => ['image/jpeg', 'image/png'],
      'mimeTypesMessage' => 'Please upload a valid image (JPEG or PNG)',
     ]),
    ],
   ])
   ->add('photos', FileType::class, [
    'label'       => 'Photos (Select up to 10)',
    'mapped'      => false,
    'required'    => false,
    'multiple'    => true,
    'constraints' => [
     new File([
      'maxSize'          => '10M',
      'mimeTypes'        => ['image/jpeg', 'image/png'],
      'mimeTypesMessage' => 'Please upload valid images (JPEG or PNG)',
     ]),
    ],
   ])
   ->add('save', SubmitType::class, ['label' => 'Save Profile']);
 }
}
