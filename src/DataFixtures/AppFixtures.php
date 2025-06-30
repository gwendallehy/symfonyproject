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
        $site1 = (new Site())->setName('Paris');
        $site2 = (new Site())->setName('Lyon');
        $manager->persist($site1);
        $manager->persist($site2);

        // États
        $states = [];
        foreach (['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'] as $libelle) {
            $etat = (new Etat())->setLibelle($libelle);
            $manager->persist($etat);
            $states[$libelle] = $etat;
        }

        // Villes
        $city = (new City())->setName('Paris')->setPostalCode(75000);
        $manager->persist($city);

        // Lieux
        $place = (new Place())
            ->setName('Parc de la Villette')
            ->setStreet('211 Avenue Jean Jaurès')
            ->setLatitude(48.889)
            ->setLongitude(2.393);
        $place->setCity($city);
        $manager->persist($place);

        // Utilisateurs
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setPseudo("user$i")
                ->setFirstname("Prénom$i")
                ->setLastname("Nom$i")
                ->setEmail("user$i@example.com")
                ->setPhone("06010203$i")
                ->setAdministrator($i === 1) // le premier est admin
                ->setActive(true)
                ->setSite($i % 2 === 0 ? $site1 : $site2)
                ->setRoles(['ROLE_USER']);

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);
            $users[] = $user;
        }

        // Sorties
        for ($j = 1; $j <= 3; $j++) {
            $outing = new Outgoing();
            $outing->setName("Sortie $j")
                ->setDateBegin((new DateTime())->modify("+$j days"))
                ->setDuration(new DateInterval('PT2H'))
                ->setDateSubscriptionLimit((new DateTime())->modify("+".($j - 1)." days"))
                ->setNbSubscriptionMax(10)
                ->setDescription("Description de la sortie $j")
                ->setEtat($states['Ouverte'])
                ->setSite($site1)
                ->setOrganizer($users[0])
                ->setPlace($place);

            $manager->persist($outing);
        }

        $manager->flush();
    }
}
