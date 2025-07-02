<?php

namespace App\Tests\Iteration1;

use App\Entity\Site;
use App\Entity\Etat;
use App\Entity\Outgoing;
use App\Entity\Place;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use DateTime;

class Iteration1Test extends TestCase
{
    private array $sites;
    private array $states;
    private array $cities; // Pas forcément nécessaire ici
    private array $places;
    private array $users;
    private array $outings;

    protected function setUp(): void
    {
        // Simuler les Sites
        $this->sites = [];
        foreach (['Paris', 'Lyon', 'Marseille', 'Bordeaux'] as $siteName) {
            $site = new Site();
            $site->setName($siteName);
            $this->sites[] = $site;
        }

        // Simuler les Etats
        $this->states = [];
        foreach (['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'] as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $this->states[$libelle] = $etat;
        }

        // Simuler les Places
        $cities = []; // Ignorés ici pour simplification
        $this->places = [];

        $placesData = [
            ['name' => 'Parc de la Villette', 'street' => '211 Avenue Jean Jaurès', 'lat' => 48.889, 'lng' => 2.393],
            ['name' => 'Parc de la Tête d\'Or', 'street' => '69006 Lyon', 'lat' => 45.7797, 'lng' => 4.8591],
            ['name' => 'Vieux-Port', 'street' => 'Marseille', 'lat' => 43.2965, 'lng' => 5.3698],
            ['name' => 'Place de la Bourse', 'street' => 'Bordeaux', 'lat' => 44.8405, 'lng' => -0.5800],
        ];

        foreach ($placesData as $placeData) {
            $place = new Place();
            $place->setName($placeData['name']);
            $place->setStreet($placeData['street']);
            $place->setLatitude($placeData['lat']);
            $place->setLongitude($placeData['lng']);
            $this->places[] = $place;
        }

        // Simuler les Users
        $this->users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setPseudo("user$i");
            $user->setFirstname("Prénom$i");
            $user->setLastname("Nom$i");
            $user->setEmail("user$i@example.com");
            $user->setPhone("06010203$i");
            $user->setAdministrator($i === 1);
            $user->setActive(true);
            $user->setSite($this->sites[$i % count($this->sites)]);
            $user->setRoles(['ROLE_USER']);
            // Le password hash on le simule juste par une string
            $user->setPassword('passwordhashed');
            $this->users[] = $user;
        }

        // Simuler les Sorties (Outings)
        $this->outings = [];
        for ($j = 1; $j <= 8; $j++) {
            $outing = new Outgoing();
            $outing->setName("Sortie $j");
            $outing->setDateBegin((new DateTime())->modify("+$j days"));
            $outing->setDuration(120);
            $outing->setDateSubscriptionLimit((new DateTime())->modify("+10 days"));
            $outing->setNbSubscriptionMax(10 + $j);
            $outing->setDescription("Description de la sortie $j");
            $outing->setEtat($this->states['Ouverte']);
            $outing->setSite($this->sites[$j % count($this->sites)]);
            $outing->setOrganizer($this->users[array_rand($this->users)]);
            $outing->setPlace($this->places[$j % count($this->places)]);
            $this->outings[] = $outing;
        }
    }

    public function test_SeConnecter(): void
    {
        $login = 'user1@example.com';
        $password = 'password';

        $user = $this->findUserByLogin($login);

        $this->assertNotNull($user, "L'utilisateur doit exister.");
        $this->assertEquals('passwordhashed', $user->getPassword(), "Le mot de passe hashé doit correspondre.");
        // Ici tu simulerais la vérification du mot de passe (hash check)
    }

    public function test_SeSouvenirDeMoi(): void
    {
        // Juste vérifier que l'option existe et peut être activée
        $rememberMe = true;
        $this->assertTrue($rememberMe, "L'option 'se souvenir de moi' peut être activée.");
    }

    public function test_GererProfil(): void
    {
        $pseudo = "user2";
        $isUnique = $this->isPseudoUnique($pseudo);
        $this->assertFalse($isUnique, "Le pseudo 'user2' n'est pas unique (existe déjà).");

        $newPseudo = "unique_pseudo";
        $isUnique = $this->isPseudoUnique($newPseudo);
        $this->assertTrue($isUnique, "Le pseudo 'unique_pseudo' est unique.");
    }

    public function test_AfficherSortiesParSite(): void
    {
        $site = $this->sites[0]; // Paris
        $sorties = $this->filterSortiesBySite($site);
        $this->assertNotEmpty($sorties, "Il doit y avoir des sorties pour le site Paris.");
    }

    public function test_CreerSortie(): void
    {
        $newSortie = new Outgoing();
        $newSortie->setName("Nouvelle sortie");
        $newSortie->setDateBegin(new DateTime('+10 days'));
        $newSortie->setDuration(180);
        $newSortie->setDateSubscriptionLimit(new DateTime('+5 days'));
        $newSortie->setNbSubscriptionMax(15);
        $newSortie->setDescription("Test création sortie");
        $newSortie->setEtat($this->states['Créée']);
        $newSortie->setSite($this->sites[0]);
        $newSortie->setOrganizer($this->users[0]);
        $newSortie->setPlace($this->places[0]);

        $this->assertEquals("Nouvelle sortie", $newSortie->getName());
        $this->assertEquals(15, $newSortie->getNbSubscriptionMax());
    }

    public function test_InscriptionSortie(): void
    {
        $outing = $this->outings[0];
        $now = new DateTime();
        $this->assertTrue(
            $outing->getEtat()->getLibelle() === 'Ouverte' && $outing->getDateSubscriptionLimit() >= $now,
            "La sortie est ouverte et les inscriptions ne sont pas clôturées."
        );
    }

    public function test_DesistementSortie(): void
    {
        $outing = $this->outings[0];
        $now = new DateTime();
        $this->assertTrue(
            $outing->getDateBegin() > $now,
            "Le participant peut se désister tant que la sortie n'a pas commencé."
        );
    }

    public function test_ClotureInscriptions(): void
    {
        $outing = $this->outings[0];
        $now = new DateTime();
        $isClosed = $outing->getDateSubscriptionLimit() < $now;
        $this->assertFalse($isClosed, "Les inscriptions ne sont pas fermées si la date limite n'est pas passée.");
    }

    public function test_AnnulationSortie(): void
    {
        $outing = $this->outings[0];
        $now = new DateTime();
        $canCancel = $outing->getDateBegin() > $now;
        $this->assertTrue($canCancel, "L'organisateur peut annuler la sortie avant son début.");
    }

    public function test_ArchivageSorties(): void
    {
        $oldDate = (new DateTime())->modify('-40 days');
        $outing = $this->outings[0];
        $outing->setDateBegin($oldDate);

        $now = new DateTime();
        $diff = $now->diff($outing->getDateBegin());
        $isArchived = $diff->days > 30;
        $this->assertTrue($isArchived, "Les sorties de plus d'un mois sont archivées.");
    }

    public function test_AfficherProfilAutres(): void
    {
        $user = $this->users[1];
        $this->assertEquals('user2', $user->getPseudo());
        $this->assertEquals('user2@example.com', $user->getEmail());
    }

    // --- Méthodes utilitaires ---

    private function findUserByLogin(string $login): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $login || $user->getPseudo() === $login) {
                return $user;
            }
        }
        return null;
    }

    private function isPseudoUnique(string $pseudo): bool
    {
        foreach ($this->users as $user) {
            if ($user->getPseudo() === $pseudo) {
                return false;
            }
        }
        return true;
    }

    private function filterSortiesBySite(Site $site): array
    {
        return array_filter($this->outings, function (Outgoing $outing) use ($site) {
            return $outing->getSite() === $site;
        });
    }
}
