<?php

namespace App\Form;

use App\Entity\UserProfile;
use App\Support\DepartmentCatalog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\All;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ageChoices = [];
        for ($i = 18; $i <= 99; $i++) {
            $ageChoices[$i] = $i;
        }

        $builder
            ->add('age', ChoiceType::class, [
                'label' => 'Âge',
                'choices' => $ageChoices,
                'placeholder' => 'Sélectionnez votre âge',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            ->add('situation', ChoiceType::class, [
                'label' => 'Situation',
                'choices' => [
                    'Célibataire' => 'Célibataire',
                    'En couple' => 'En couple',
                    'Couple libre' => 'Couple libre'
                ],
                'placeholder' => 'Sélectionnez votre situation',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            // ✅ CHECKBOX / TAG STYLE READY
            ->add('research', ChoiceType::class, [
                'label' => 'Je recherche',
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    'Couple' => 'Couple'
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label_attr' => ['class' => 'checkbox-label'],
            ])

            ->add('biography', TextareaType::class, [
                'label' => 'Bio',
                'required' => false,
                'attr' => [
                    'class' => 'form-control textarea',
                    'placeholder' => 'Parle un peu de toi...'
                ],
            ])

            ->add('department', ChoiceType::class, [
                'label' => 'Département',
                'choices' => DepartmentCatalog::CHOICES,
                'placeholder' => 'Choisir un département',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ta ville'
                ],
            ])

            ->add('avatarFile', FileType::class, [
                'label' => 'Avatar',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '8M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                    ]),
                ],
            ])

            ->add('photoFiles', FileType::class, [
                'label' => 'Photos',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new Assert\File([
                                'maxSize' => '8M',
                                'mimeTypes' => ['image/jpeg', 'image/png'],
                            ])
                        ]
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
