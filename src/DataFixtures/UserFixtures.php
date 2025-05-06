<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
 private UserPasswordHasherInterface $passwordHasher;

 public function __construct(UserPasswordHasherInterface $passwordHasher)
 {
  $this->passwordHasher = $passwordHasher;
 }

 public function load(ObjectManager $manager): void
 {
  $faker = Factory::create('fr_FR');

  for ($i = 1; $i <= 15; $i++) {
   // Création de l'utilisateur
   $user = new User();
   $user->setEmail("user$i@example.com");
   $user->setPseudo($faker->userName);
   $user->setRoles(['ROLE_USER']);
   $hashedPassword = $this->passwordHasher->hashPassword($user, 'Nicolas');
   $user->setPassword($hashedPassword);

   // Création du profil associé
   $profile = new UserProfile();
   $profile->setUser($user);
   $profile->setPhotos([]); // Aucune photo
   $profile->setAvatar(null); // Aucun avatar

   // Persistance des entités
   $manager->persist($user);
   $manager->persist($profile);
  }

  $manager->flush();
 }
}
