<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserInterest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class InterestController extends AbstractController
{
 #[Route('/interet/{id}', name: 'app_user_interest')]
 public function markInterest(User $user, EntityManagerInterface $em): Response
 {
  $currentUser = $this->getUser();

  // Empêche l'auto-like
  if ($user === $currentUser) {
   $this->addFlash('warning', 'Tu ne peux pas t’aimer toi-même !');
   return $this->redirectToRoute('app_profiles_list');
  }

  $interest = new UserInterest();
  $interest->setSourceUser($currentUser);
  $interest->setTargetUser($user);
  $interest->setCreatedAt(new \DateTime());

  $em->persist($interest);
  $em->flush();

  $this->addFlash('success', 'Profil marqué comme intéressant !');
  return $this->redirectToRoute('app_profiles_list');
 }
}
