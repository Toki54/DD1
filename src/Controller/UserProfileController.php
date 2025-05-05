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
   return $this->redirectToRoute('app_login'); // Redirige vers la page de connexion
  }

  // Assure-toi que le profil existe
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
    $photoPaths = [];
    foreach ($photoFiles as $photo) {
     $photoFilename = uniqid() . '.' . $photo->guessExtension();
     $photo->move($this->getParameter('photos_directory'), $photoFilename);
     $photoPaths[] = $photoFilename;
    }
    $userProfile->setPhotos(array_merge($userProfile->getPhotos(), $photoPaths));
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

 #[Route('/profile/{id}', name: 'app_profile_view')]
public function view(int $id, EntityManagerInterface $entityManager): Response
{
    $userProfile = $entityManager->getRepository(UserProfile::class)->find($id);

    if (!$userProfile) {
        throw $this->createNotFoundException('Profil non trouvé.');
    }

    return $this->render('profile/view.html.twig', [
        'userProfile' => $userProfile,
    ]);
}

 #[Route('/profiles/{id?}', name: 'app_profiles_list')]
    public function list(EntityManagerInterface $entityManager, Request $request, ?int $id): Response
    {
        // Récupération des filtres depuis la requête
        $sortSex       = $request->query->get('sex', null);
        $sortSituation = $request->query->get('situation', null);
        
        $sortCity      = $request->query->get('city', null);
        $sortResearch  = $request->query->get('research', null);

        $criteria = [];
        
        // Application des filtres sur les critères
        if ($sortSex) {
            $criteria['sex'] = $sortSex;
        }
        if ($sortSituation) {
            $criteria['situation'] = $sortSituation;
        }
        
        if ($sortCity) {
            $criteria['city'] = $sortCity;
        }
        if ($sortResearch) {
            $criteria['research'] = $sortResearch;
        }

        // Récupération des profils filtrés
        $profiles = $entityManager->getRepository(UserProfile::class)->findBy($criteria);

        $selectedProfile = null;
        if ($id) {
            $selectedProfile = $entityManager->getRepository(UserProfile::class)->find($id);
        }

        return $this->render('profile/list.html.twig', [
            'profiles'        => $profiles,
            'selectedProfile' => $selectedProfile,
            'sex'             => $sortSex,
            'situation'       => $sortSituation,
            
            'city'            => $sortCity,
            'research'        => $sortResearch,
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
