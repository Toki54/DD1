<?php

namespace App\Controller;

use App\Entity\UserProfile;
use App\Entity\ProfileVisit;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\ProfileLike;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        // ✅ Abonné = ROLE_PREMIUM
        $isSubscribed = in_array('ROLE_PREMIUM', $user->getRoles(), true);

        return $this->render('profile/show.html.twig', [
            'userProfile'  => $userProfile,
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
                $photoPaths = [];

                foreach ($photoFiles as $photo) {
                    if (count($existingPhotos) + count($photoPaths) >= 10) {
                        $this->addFlash('error', 'Vous ne pouvez pas ajouter plus de 10 photos.');
                        break;
                    }

                    if ($photo->getSize() > 8 * 1024 * 1024) {
                        $this->addFlash('error', 'Une photo dépasse 8 Mo et n’a pas été uploadée.');
                        continue;
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

        // ✅ Abonné = ROLE_PREMIUM (celui qui visite)
        $isSubscribed = false;
        $user = $this->getUser();
        if ($user) {
            $isSubscribed = in_array('ROLE_PREMIUM', $user->getRoles(), true);
        }

        // Visite
        if ($this->getUser()) {
            $visitorProfile = $this->getUser()->getProfile();
            if ($visitorProfile && $visitorProfile !== $userProfile) {
                $visit = new ProfileVisit();
                $visit->setVisited($userProfile);
                $visit->setVisitor($visitorProfile);
                $entityManager->persist($visit);
                $entityManager->flush();
            }
        }

        return $this->render('profile/view.html.twig', [
            'userProfile'  => $userProfile,
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

        $user = $this->getUser();
        $isSubscribed = $user ? in_array('ROLE_PREMIUM', $user->getRoles(), true) : false;

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

    #[Route('/profile/like/{id}', name: 'app_profile_like', methods: ['POST'])]
    public function like(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté.');
        }

        $likerProfile = $user->getProfile();
        if (!$likerProfile) {
            return new JsonResponse(['error' => 'Profil introuvable'], 400);
        }

        $likedProfile = $entityManager->getRepository(UserProfile::class)->find($id);
        if (!$likedProfile) {
            return new JsonResponse(['error' => 'Profil aimé introuvable'], 404);
        }

        if ($likerProfile === $likedProfile) {
            return new JsonResponse(['error' => 'Vous ne pouvez pas liker votre propre profil'], 400);
        }

        $existingLike = $entityManager->getRepository(ProfileLike::class)->findOneBy([
            'liker' => $likerProfile,
            'liked' => $likedProfile,
        ]);

        if ($existingLike) {
            return new JsonResponse(['message' => 'Déjà liké'], 200);
        }

        $like = new ProfileLike();
        $like->setLiker($likerProfile);
        $like->setLiked($likedProfile);
        $entityManager->persist($like);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Profil liké avec succès']);
    }

    #[Route('/profile/unlike/{id}', name: 'app_profile_unlike', methods: ['POST'])]
    public function unlike(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté.');
        }

        $likerProfile = $user->getProfile();
        if (!$likerProfile) {
            return new JsonResponse(['error' => 'Profil introuvable'], 400);
        }

        $likedProfile = $entityManager->getRepository(UserProfile::class)->find($id);
        if (!$likedProfile) {
            return new JsonResponse(['error' => 'Profil aimé introuvable'], 404);
        }

        $existingLike = $entityManager->getRepository(ProfileLike::class)->findOneBy([
            'liker' => $likerProfile,
            'liked' => $likedProfile,
        ]);

        if (!$existingLike) {
            return new JsonResponse(['message' => 'Profil non liké'], 200);
        }

        $entityManager->remove($existingLike);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Like supprimé']);
    }

    #[Route('/profile/likes', name: 'app_profile_likes')]
    public function likes(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos likes.');
            return $this->redirectToRoute('app_login');
        }

        $userProfile = $user->getProfile();

        $likes = $entityManager->getRepository(ProfileLike::class)
            ->createQueryBuilder('pl')
            ->where('pl.liked = :profile')
            ->setParameter('profile', $userProfile)
            ->orderBy('pl.id', 'DESC')
            ->getQuery()
            ->getResult();

        $likers = array_map(fn($like) => $like->getLiker(), $likes);

        $qb = $entityManager->getRepository(ProfileVisit::class)
            ->createQueryBuilder('v');

        $subQuery = $entityManager->createQueryBuilder()
            ->select('MAX(v2.visitedAt)')
            ->from(ProfileVisit::class, 'v2')
            ->where('v2.visitor = v.visitor')
            ->andWhere('v2.visited = :profile');

        $visits = $qb
            ->where('v.visited = :profile')
            ->setParameter('profile', $userProfile)
            ->andWhere($qb->expr()->eq('v.visitedAt', '(' . $subQuery->getDQL() . ')'))
            ->orderBy('v.visitedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $visitorProfiles = array_map(fn($visit) => $visit->getVisitor(), $visits);

        return $this->render('profile/likes_and_visits.html.twig', [
            'visitorProfiles' => $visitorProfiles,
            'likerProfiles'   => $likers,
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
}
