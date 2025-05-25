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
  $user = $this->getUser();

  if (!$user) {
   $this->addFlash('error', 'Vous devez être connecté pour voir votre profil.');
   return $this->redirectToRoute('app_login');
  }

  $userProfile = $user->getProfile();

  if (!$userProfile) {
   $userProfile = new UserProfile();
   $userProfile->setUser($user);
   $entityManager->persist($userProfile);
   $entityManager->flush();
  }

  $isSubscribed = in_array('ROLE_PREMIUM', $user->getRoles());

  // Récupère les photos à afficher, floutées si non abonné
  $photosToDisplay = $this->getPhotosForDisplay($userProfile, $isSubscribed);

  return $this->render('profile/show.html.twig', [
   'userProfile'  => $userProfile,
   'photos'       => $photosToDisplay,
   'isSubscribed' => $isSubscribed,
  ]);
 }

 #[Route('/profile/edit', name: 'app_profile_edit')]
 public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
 {
  $user = $this->getUser();

  if (!$user) {
   $this->addFlash('error', 'Vous devez être connecté pour modifier votre profil.');
   return $this->redirectToRoute('app_login');
  }

  $userProfile = $user->getProfile();

  if (!$userProfile) {
   $userProfile = new UserProfile();
   $userProfile->setUser($user);
   $entityManager->persist($userProfile);
   $entityManager->flush();
  }

  $form = $this->createForm(UserProfileType::class, $userProfile);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   $avatarFile = $form->get('avatarFile')->getData();
   if ($avatarFile) {
    $avatarFilename = uniqid() . '.' . $avatarFile->guessExtension();
    $avatarFile->move($this->getParameter('avatars_directory'), $avatarFilename);
    $userProfile->setAvatar($avatarFilename);
   }

   $photoFiles = $form->get('photoFiles')->getData();
   if ($photoFiles) {
    $existingPhotos = $userProfile->getPhotos() ?? [];
    $photoPaths     = [];

    foreach ($photoFiles as $photo) {
     if (count($existingPhotos) + count($photoPaths) >= 10) {
      $this->addFlash('error', 'Vous ne pouvez pas ajouter plus de 10 photos.');
      break;
     }

     $photoFilename = uniqid() . '.' . $photo->guessExtension();
     $photo->move($this->getParameter('photos_directory'), $photoFilename);
     $photoPaths[] = $photoFilename;
    }

    $userProfile->setPhotos(array_merge($existingPhotos, $photoPaths));
   }

   $entityManager->flush();
   $this->addFlash('success', 'Profil mis à jour avec succès !');
   return $this->redirectToRoute('app_profile_edit');
  }

  return $this->render('profile/edit.html.twig', [
   'form'        => $form->createView(),
   'userProfile' => $userProfile,
  ]);
 }

 #[Route('/profile/{id}', name: 'app_profile_view', requirements: ['id' => '\d+'])]
 public function view(int $id, EntityManagerInterface $entityManager): Response
 {
  $userProfile = $entityManager->getRepository(UserProfile::class)->find($id);

  if (!$userProfile) {
   throw $this->createNotFoundException('Profil non trouvé.');
  }

  $isSubscribed = false;
  $user         = $this->getUser();
  if ($user && method_exists($user, 'getSubscription')) {
   $isSubscribed = (bool) $user->getSubscription();
  }

  $photosToDisplay = $this->getPhotosForDisplay($userProfile, $isSubscribed);

  return $this->render('profile/view.html.twig', [
   'userProfile'  => $userProfile,
   'photos'       => $photosToDisplay,
   'isSubscribed' => $isSubscribed,
  ]);
 }

 #[Route('/profiles/{id?}', name: 'app_profiles_list')]
 public function list(Request $request, EntityManagerInterface $entityManager, ?int $id): Response
 {
  $sexFilters       = $request->query->all('sex');
  $situationFilters = $request->query->all('situation');
  $city             = $request->query->get('city');
  $researchFilters  = $request->query->all('research');

  $qb = $entityManager->getRepository(UserProfile::class)->createQueryBuilder('p');

  if (!empty($sexFilters)) {
   $qb->andWhere('p.sex IN (:sex)')->setParameter('sex', $sexFilters);
  }

  if (!empty($situationFilters)) {
   $qb->andWhere('p.situation IN (:situation)')->setParameter('situation', $situationFilters);
  }

  if (!empty($city)) {
   $qb->andWhere('p.city = :city')->setParameter('city', $city);
  }

  if (!empty($researchFilters)) {
   $orX = $qb->expr()->orX();
   foreach ($researchFilters as $key => $val) {
    $orX->add($qb->expr()->like('p.research', ':research_' . $key));
    $qb->setParameter('research_' . $key, '%"' . $val . '"%');
   }
   $qb->andWhere($orX);
  }

  $qb->orderBy('p.id', 'DESC');
  $profiles = $qb->getQuery()->getResult();

  $selectedProfile = null;
  if ($id) {
   $selectedProfile = $entityManager->getRepository(UserProfile::class)->find($id);
  }

  $user         = $this->getUser();
  $isSubscribed = $user && method_exists($user, 'getSubscription') && $user->getSubscription() ? true : false;

  // Remplace les photos des profils par la version floutée si non abonné
  foreach ($profiles as $profile) {
   $photos = $this->getPhotosForDisplay($profile, $isSubscribed);
   $profile->setPhotos($photos); // Attention : si getPhotos() est persistant, créer un setter temporaire ou une variable temporaire
  }

  return $this->render('profile/list.html.twig', [
   'profiles'        => $profiles,
   'selectedProfile' => $selectedProfile,
   'sex'             => $sexFilters,
   'situation'       => $situationFilters,
   'city'            => $city,
   'research'        => $researchFilters,
   'isSubscribed'    => $isSubscribed,
  ]);
 }

 #[Route('/profile/delete-photo/{photoFilename}', name: 'app_profile_delete_photo')]
 public function deletePhoto(string $photoFilename, EntityManagerInterface $entityManager): Response
 {
  $user = $this->getUser();

  if (!$user) {
   $this->addFlash('error', 'Vous devez être connecté pour supprimer une photo.');
   return $this->redirectToRoute('app_login');
  }

  $userProfile = $user->getProfile();

  if (!$userProfile) {
   $this->addFlash('error', 'Profil non trouvé.');
   return $this->redirectToRoute('app_profile_show');
  }

  if (in_array($photoFilename, $userProfile->getPhotos())) {
   $userProfile->setPhotos(array_diff($userProfile->getPhotos(), [$photoFilename]));

   $photoPath = $this->getParameter('photos_directory') . '/' . $photoFilename;
   if (file_exists($photoPath)) {
    unlink($photoPath);
   }

   $entityManager->flush();
   $this->addFlash('success', 'Photo supprimée avec succès.');
  } else {
   $this->addFlash('error', 'Photo non trouvée.');
  }

  return $this->redirectToRoute('app_profile_edit');
 }

 /**
  * Retourne le tableau de photos à afficher, floutées si utilisateur non abonné
  */
 private function getPhotosForDisplay(UserProfile $profile, bool $isSubscribed): array
 {
  $photos = $profile->getPhotos() ?? [];

  if ($isSubscribed) {
   // Utilisateur abonné : renvoie toutes les photos originales
   return $photos;
  }

  // Utilisateur non abonné : on ne renvoie que la 1ère photo floutée, les autres masquées

  if (empty($photos)) {
   return [];
  }

  // Exemple : on renvoie une version floutée de la première photo
  // ATTENTION : il faut avoir pré-généré une version floutée en amont,
  // ou utiliser une image floue par défaut (ex: 'blurred_placeholder.jpg')
  // ici on suppose que la version floutée porte un préfixe 'blurred_' + filename

  $blurredPhotos = [];

  $firstPhoto       = $photos[0];
  $blurredPhotoPath = 'blurred_' . $firstPhoto;

  $blurredPhotoFullPath = $this->getParameter('photos_directory') . '/' . $blurredPhotoPath;
  if (file_exists($blurredPhotoFullPath)) {
   $blurredPhotos[] = $blurredPhotoPath;
  } else {
   // Si pas de version floutée dispo, on peut utiliser une image floue générique
   $blurredPhotos[] = 'blurred_placeholder.jpg'; // à placer dans le dossier photos
  }

  return $blurredPhotos;
 }
}
