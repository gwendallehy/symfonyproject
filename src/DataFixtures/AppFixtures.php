<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Etat;
use App\Entity\Outgoing;
use App\Entity\Place;
use App\Entity\Site;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Sites
        $sitesData = ['Quimper', 'Rennes', 'Niort', 'Nantes'];
        $sites = [];
        foreach ($sitesData as $siteName) {
            $site = (new Site())->setName($siteName);
            $manager->persist($site);
            $sites[] = $site;
        }

        // États
        $states = [];
        foreach (['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'] as $libelle) {
            $etat = (new Etat())->setLibelle($libelle);
            $manager->persist($etat);
            $states[$libelle] = $etat;
        }

        // Villes
        $citiesData = [
            ['name' => 'Quimper', 'postalCode' => 29000],
            ['name' => 'Rennes', 'postalCode' => 35131],
            ['name' => 'Niort', 'postalCode' => 79000],
            ['name' => 'Nantes', 'postalCode' => 44800],
        ];
        $cities = [];
        foreach ($citiesData as $cityData) {
            $city = (new City())
                ->setName($cityData['name'])
                ->setPostalCode($cityData['postalCode']);
            $manager->persist($city);
            $cities[] = $city;
        }

        // Lieux (Places)
        $placesData = [
            ['name' => 'Parc de la Villette', 'street' => '211 Avenue Jean Jaurès', 'lat' => 48.889, 'lng' => 2.393, 'city' => $cities[0]],
            ['name' => 'Parc de la Tête d\'Or', 'street' => '69006 Lyon', 'lat' => 45.7797, 'lng' => 4.8591, 'city' => $cities[1]],
            ['name' => 'Vieux-Port', 'street' => 'Marseille', 'lat' => 43.2965, 'lng' => 5.3698, 'city' => $cities[2]],
            ['name' => 'Place de la Bourse', 'street' => 'Bordeaux', 'lat' => 44.8405, 'lng' => -0.5800, 'city' => $cities[3]],
        ];
        $places = [];
        foreach ($placesData as $placeData) {
            $place = (new Place())
                ->setName($placeData['name'])
                ->setStreet($placeData['street'])
                ->setLatitude($placeData['lat'])
                ->setLongitude($placeData['lng'])
                ->setCity($placeData['city']);
            $manager->persist($place);
            $places[] = $place;
        }

        // Utilisateurs
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setPseudo("user$i")
                ->setFirstname("Prénom$i")
                ->setLastname("Nom$i")
                ->setEmail("user$i@example.com")
                ->setPhone("06010203$i")
                ->setAdministrator($i === 1)
                ->setActive(true)
                ->setSite($sites[$i % count($sites)])
                ->setRoles(['ROLE_USER']);

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);
            $users[] = $user;
        }

        // Sorties (Outings)
        $outingCount = 8;
        for ($j = 1; $j <= $outingCount; $j++) {
            $outing = new Outgoing();
            $outing->setName("Sortie $j")
                ->setDateBegin((new DateTime())->modify("+$j days"))
                ->setDuration(120)// 2 heures = 120 minutes
                ->setDateSubscriptionLimit((new DateTime())->modify("+".($j - 1)." days"))
                ->setNbSubscriptionMax(10 + $j)
                ->setDescription("Description de la sortie $j")
                ->setEtat($states['Ouverte'])
                ->setSite($sites[$j % count($sites)])
                ->setOrganizer($users[array_rand($users)])
                ->setPlace($places[$j % count($places)]);

            $manager->persist($outing);
        }

        $manager->flush();
    }
}
