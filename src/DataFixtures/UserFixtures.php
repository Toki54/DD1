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
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker           = Factory::create('fr_FR');
        $researchOptions = ['Homme', 'Femme', 'Couple'];

        for ($i = 1; $i <= 20; $i++) {
            $user = new User();

            $email    = "user{$i}@mail.fr";
            $password = $this->hasher->hashPassword($user, 'password');

            $user->setEmail($email)
                ->setPassword($password)
                ->setPseudo("user{$i}")
                ->setRoles(['ROLE_USER']);

            // Génère entre 1 et 5 photos parmi 1.jpg à 10.jpg
            $photoCount = rand(1, 5);
            $photoList  = [];
            $used       = [];

            while (count($photoList) < $photoCount) {
                $num = rand(1, 10);
                if (!in_array($num, $used)) {
                    $photoList[] = $num . '.jpg';
                    $used[]      = $num;
                }
            }

            $profile = new UserProfile();
            $profile->setSex($faker->randomElement(['Homme', 'Femme', 'Couple']))
                ->setSituation($faker->randomElement(['Célibataire', 'En couple', 'Couple libre']))
                ->setResearch($faker->randomElements($researchOptions, rand(1, 3)))
                ->setBiography($faker->paragraph())
                ->setAvatar('default-avatar.png')
                ->setPhotos($photoList)
                ->setDepartment($faker->randomElement([
    "01 - Ain", "02 - Aisne", "03 - Allier", "04 - Alpes-de-Haute-Provence", "05 - Hautes-Alpes",
    "06 - Alpes-Maritimes", "07 - Ardèche", "08 - Ardennes", "09 - Ariège", "10 - Aube",
    "11 - Aude", "12 - Aveyron", "13 - Bouches-du-Rhône", "14 - Calvados", "15 - Cantal",
    "16 - Charente", "17 - Charente-Maritime", "18 - Cher", "19 - Corrèze", "2A - Corse-du-Sud",
    "2B - Haute-Corse", "21 - Côte-d'Or", "22 - Côtes-d'Armor", "23 - Creuse", "24 - Dordogne",
    "25 - Doubs", "26 - Drôme", "27 - Eure", "28 - Eure-et-Loir", "29 - Finistère",
    "30 - Gard", "31 - Haute-Garonne", "32 - Gers", "33 - Gironde", "34 - Hérault",
    "35 - Ille-et-Vilaine", "36 - Indre", "37 - Indre-et-Loire", "38 - Isère", "39 - Jura",
    "40 - Landes", "41 - Loir-et-Cher", "42 - Loire", "43 - Haute-Loire", "44 - Loire-Atlantique",
    "45 - Loiret", "46 - Lot", "47 - Lot-et-Garonne", "48 - Lozère", "49 - Maine-et-Loire",
    "50 - Manche", "51 - Marne", "52 - Haute-Marne", "53 - Mayenne", "54 - Meurthe-et-Moselle",
    "55 - Meuse", "56 - Morbihan", "57 - Moselle", "58 - Nièvre", "59 - Nord",
    "60 - Oise", "61 - Orne", "62 - Pas-de-Calais", "63 - Puy-de-Dôme", "64 - Pyrénées-Atlantiques",
    "65 - Hautes-Pyrénées", "66 - Pyrénées-Orientales", "67 - Bas-Rhin", "68 - Haut-Rhin", "69 - Rhône",
    "70 - Haute-Saône", "71 - Saône-et-Loire", "72 - Sarthe", "73 - Savoie", "74 - Haute-Savoie",
    "75 - Paris", "76 - Seine-Maritime", "77 - Seine-et-Marne", "78 - Yvelines", "79 - Deux-Sèvres",
    "80 - Somme", "81 - Tarn", "82 - Tarn-et-Garonne", "83 - Var", "84 - Vaucluse",
    "85 - Vendée", "86 - Vienne", "87 - Haute-Vienne", "88 - Vosges", "89 - Yonne",
    "90 - Territoire de Belfort", "91 - Essonne", "92 - Hauts-de-Seine", "93 - Seine-Saint-Denis",
    "94 - Val-de-Marne", "95 - Val-d'Oise"
]))
                ->setCity($faker->city())
                ->setBirthdate($faker->dateTimeBetween('-50 years', '-18 years'))
                ->setUser($user);

            $user->setProfile($profile);

            $manager->persist($user);
            $manager->persist($profile);
        }

        $manager->flush();
    }
}
