<?php

namespace App\Form;

use App\Entity\UserProfile;
use App\Support\CityCatalog;
use App\Support\DepartmentCatalog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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

        $profile = $options['data'] instanceof UserProfile ? $options['data'] : null;

        $addCityField = static function (
            FormBuilderInterface|FormInterface $form,
            ?string $department,
            ?string $currentCity = null,
        ): void {
            $cities = CityCatalog::forDepartment($department);

            if ($currentCity !== null && $currentCity !== '' && !in_array($currentCity, $cities, true)) {
                $cities[] = $currentCity;
                sort($cities, SORT_NATURAL | SORT_FLAG_CASE);
            }

            $form->add('city', ChoiceType::class, [
                'label' => 'Ville',
                'choices' => array_combine($cities, $cities) ?: [],
                'placeholder' => $department ? 'Choisir une ville' : 'Choisir d’abord un département',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);
        };

        $addCityField(
            $builder,
            $profile?->getDepartment(),
            $profile?->getCity(),
        );

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) use ($addCityField): void {
            $data = $event->getData();
            $department = is_array($data) ? trim((string) ($data['department'] ?? '')) : '';

            $addCityField($event->getForm(), $department ?: null);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
