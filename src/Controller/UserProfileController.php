<?php

namespace App\Controller;

use App\Entity\UserProfile;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

        // Vérifie si l'utilisateur est abonné (ROLE_PREMIUM par exemple)
        $isSubscribed = in_array('ROLE_PREMIUM', $user->getRoles());

        return $this->render('profile/show.html.twig', [
            'userProfile' => $userProfile,
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

  // Vérifie si le profil existe, sinon crée-le
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
   'userProfile' => $userProfile, // Assure-toi que la variable userProfile est passée à la vue
  ]);
 }

 #[Route('/profile/{id}', name: 'app_profile_view', requirements: ['id' => '\d+'])]
 public function view(int $id, EntityManagerInterface $entityManager): Response
 {
  $userProfile = $entityManager->getRepository(UserProfile::class)->find($id);

  if (!$userProfile) {
   throw $this->createNotFoundException('Profil non trouvé.');
  }

  // Vérifie si l'utilisateur est abonné
  $isSubscribed = $this->getUser() && $this->getUser()->getSubscription() ? true : false;

  return $this->render('profile/view.html.twig', [
   'userProfile'  => $userProfile,
   'isSubscribed' => $isSubscribed,
  ]);
 }

 #[Route('/profiles/{id?}', name: 'app_profiles_list')]
public function list(Request $request, EntityManagerInterface $entityManager, ?int $id): Response
{
    // Récupération des filtres
    $sexFilters       = $request->query->all('sex');
    $situationFilters = $request->query->all('situation');
    $city             = $request->query->get('city');
    $researchFilters  = $request->query->all('research');

    // Construction de la requête
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


    // On peut trier les profils par date de création ou mise à jour si les colonnes existent
    $qb->orderBy('p.id', 'DESC');

    $profiles = $qb->getQuery()->getResult();

    // Profil sélectionné si on a passé un ID dans l’URL
    $selectedProfile = null;
    if ($id) {
        $selectedProfile = $entityManager->getRepository(UserProfile::class)->find($id);
    }

    // Vérifie si l'utilisateur actuel est abonné
    $isSubscribed = $this->getUser() && $this->getUser()->getSubscription() ? true : false;

    return $this->render('profile/list.html.twig', [
        'profiles'         => $profiles,
        'selectedProfile'  => $selectedProfile,
        'sex'              => $sexFilters,
        'situation'        => $situationFilters,
        'city'             => $city,
        'research'         => $researchFilters,
        'isSubscribed'     => $isSubscribed,
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

  // Vérifie si la photo existe dans le profil
  if (in_array($photoFilename, $userProfile->getPhotos())) {
   // Retirer la photo du tableau
   $userProfile->setPhotos(array_diff($userProfile->getPhotos(), [$photoFilename]));

   // Supprimer le fichier de l'upload
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

}
