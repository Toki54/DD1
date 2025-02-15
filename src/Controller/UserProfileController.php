<?php

namespace App\Controller;

use App\Entity\UserProfile;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserProfileController extends AbstractController
{
 #[Route('/profile', name: 'app_profile_show')]
 public function show(EntityManagerInterface $entityManager): Response
 {
  $user        = $this->getUser();
  $userProfile = $user->getProfile();

  if (!$userProfile) {
   $userProfile = new UserProfile();
   $userProfile->setUser($user);
   $entityManager->persist($userProfile);
   $entityManager->flush();
  }

  return $this->render('profile/show.html.twig', [
   'userProfile' => $userProfile,
  ]);
 }

 #[Route('/profile/edit', name: 'app_profile_edit')]
 public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
 {
  $user        = $this->getUser();
  $userProfile = $user->getProfile();

  if (!$userProfile) {
   $userProfile = new UserProfile();
   $userProfile->setUser($user);
   $entityManager->persist($userProfile);
  }

  $form = $this->createForm(UserProfileType::class, $userProfile);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   // Gestion de l'avatar
   $avatarFile = $form->get('avatarFile')->getData();
   if ($avatarFile) {
    $avatarFilename = uniqid() . '.' . $avatarFile->guessExtension();
    $avatarFile->move($this->getParameter('avatars_directory'), $avatarFilename);
    $userProfile->setAvatar($avatarFilename);
   }

   // Gestion des photos
   $photoFiles = $form->get('photoFiles')->getData();
   if ($photoFiles) {
    $photoPaths = [];
    foreach ($photoFiles as $photo) {
     $photoFilename = uniqid() . '.' . $photo->guessExtension();
     $photo->move($this->getParameter('photos_directory'), $photoFilename);
     $photoPaths[] = $photoFilename;
    }
    $userProfile->setPhotos(array_merge($userProfile->getPhotos(), $photoPaths));

   }

   $entityManager->flush();
   $this->addFlash('success', 'Profile updated successfully!');
   return $this->redirectToRoute('app_profile_edit');
  }

  return $this->render('profile/edit.html.twig', [
   'form' => $form->createView(),
  ]);
 }
}
