<?php

namespace App\Controller;

use App\Entity\UserProfile;
use App\Form\UserProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserProfileController extends AbstractController
{
 #[Route('/profile', name: 'profile_show')]
 public function show()
 {
  $userProfile = $this->getUser()->getProfile(); // Accède au profil de l'utilisateur

  // Si le profil n'existe pas, on redirige vers une page de création
  if (!$userProfile) {
   return $this->redirectToRoute('profile_edit');
  }

  return $this->render('profile/show.html.twig', [
   'userProfile' => $userProfile,
  ]);
 }

 #[Route('/profile/edit', name: 'profile_edit')]
 public function edit(Request $request)
 {
  $user        = $this->getUser();
  $userProfile = $user->getProfile();

  // Si le profil n'existe pas, on en crée un pour l'utilisateur
  if (!$userProfile) {
   $userProfile = new UserProfile();
   $userProfile->setUser($user); // Lier le profil à l'utilisateur
  }

  $form = $this->createForm(UserProfileType::class, $userProfile);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   // Gérer les téléchargements d'avatar et de photos
   $avatarFile = $form->get('avatar')->getData();
   if ($avatarFile) {
    $avatarFilename = uniqid() . '.' . $avatarFile->guessExtension();
    $avatarFile->move($this->getParameter('avatars_directory'), $avatarFilename);
    $userProfile->setAvatar($avatarFilename); // Enregistrer l'avatar dans le profil
   }

   $photos = $form->get('photos')->getData();
   if ($photos) {
    $photoPaths = [];
    foreach ($photos as $photo) {
     $photoFilename = uniqid() . '.' . $photo->guessExtension();
     $photo->move($this->getParameter('photos_directory'), $photoFilename);
     $photoPaths[] = $photoFilename; // Enregistrer les chemins des photos dans le profil
    }
    $userProfile->setPhotos($photoPaths);
   }

   // Sauvegarder les données
   $entityManager = $this->getDoctrine()->getManager();
   $entityManager->persist($userProfile); // Persister le profil mis à jour
   $entityManager->flush(); // Appliquer les changements en base

   $this->addFlash('success', 'Profile updated successfully!');
   return $this->redirectToRoute('profile_show'); // Rediriger vers l'affichage du profil
  }

  return $this->render('profile/edit.html.twig', [
   'form' => $form->createView(),
  ]);
 }
}
