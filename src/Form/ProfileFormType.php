<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Le champ email
            ->add('email', EmailType::class)

            // Le champ sexe
            ->add('sexe', ChoiceType::class, [
                'choices'  => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                ],
                'required' => false,
                'placeholder' => 'Choisir le sexe'
            ])

            // Le champ situation
            ->add('situation', ChoiceType::class, [
                'choices'  => [
                    'Célibataire' => 'celibataire',
                    'En couple' => 'couple',
                ],
                'required' => false,
                'placeholder' => 'Choisir la situation'
            ])

            // Le champ bio
            ->add('bio', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5]
            ])

            // Le champ recherche
            ->add('recherche', ChoiceType::class, [
                'choices'  => [
                    'Femme' => 'femme',
                    'Homme' => 'homme',
                ],
                'required' => false,
                'placeholder' => 'Rechercher'
            ])

            // Le champ avatar
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (image PNG, JPG, JPEG)',
                'mapped' => false,  // Ce champ n'est pas mappé directement à l'entité User
                'required' => false,  // Il est optionnel
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image (PNG, JPG, JPEG)',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
