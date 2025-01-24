<?php

// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // Crée un formulaire de profil à partir des données de l'utilisateur
        $form = $this->createForm(ProfileFormType::class, $user);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'avatar si présent
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->getExtension();
                try {
                    // Déplace le fichier dans le répertoire des avatars
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    // Met à jour le champ avatar dans l'utilisateur
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'avatar');
                }
            }

            // Sauvegarder les données modifiées de l'utilisateur
            $entityManager->flush();  // Pas besoin de persist, car l'entité est déjà suivie

            // Afficher un message de succès
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès!');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
}

