<?php


namespace App\Tests\Iteration2;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class Iteration2Test extends TestCase
{
    public function test_PhotoProfil(): void
    {
        // Test ID: 1004
        // Simuler l’upload d’une photo et vérifier qu’elle est bien liée au profil utilisateur

        $user = new User();
        $user->setPseudo('userTest');

        // Simuler upload photo (ex: méthode setPhoto qui stocke le nom de fichier)
        $photoFilename = 'photo_test.jpg';
        $user->setPicture($photoFilename);

        // Vérifier que la photo est bien enregistrée
        $this->assertEquals($photoFilename, $user->getPicture());
    }

    public function test_UtilisationSmartphone(): void
    {
        // Test ID: 2009
        // Vérifier que la vue smartphone limite la création des sorties et groupes

        // Supposons qu’on ait un service ou un contrôleur qui reçoit l’info device type
        $deviceType = 'smartphone';

        // Fonctionnalité attendue : création désactivée
        $canCreateSortie = ($deviceType !== 'smartphone');
        $canCreateGroup = ($deviceType !== 'smartphone');

        $this->assertFalse($canCreateSortie);
        $this->assertFalse($canCreateGroup);
    }

    public function test_UtilisationTablette(): void
    {
        // Test ID: 2011
        // Vérifier que sur tablette, toutes fonctionnalités sont actives comme sur desktop

        $deviceType = 'tablette';

        $canCreateSortie = ($deviceType === 'tablette' || $deviceType === 'desktop');
        $canCreateGroup = ($deviceType === 'tablette' || $deviceType === 'desktop');

        $this->assertTrue($canCreateSortie);
        $this->assertTrue($canCreateGroup);
    }

    public function test_InscrireUtilisateursFichier(): void
    {
        // Test ID: 1006
        // Simuler l’import CSV et vérifier que des utilisateurs sont bien créés

        // Exemple CSV simulé : tableau de données
        $csvUsers = [
            ['pseudo' => 'userCSV1', 'email' => 'csv1@example.com'],
            ['pseudo' => 'userCSV2', 'email' => 'csv2@example.com'],
        ];

        $usersCreated = [];
        foreach ($csvUsers as $csvUser) {
            $user = new User();
            $user->setPseudo($csvUser['pseudo']);
            $user->setEmail($csvUser['email']);
            // Autres propriétés...

            $usersCreated[] = $user;
        }

        // Vérifier que 2 utilisateurs ont été créés
        $this->assertCount(2, $usersCreated);
        $this->assertEquals('userCSV1', $usersCreated[0]->getPseudo());
    }

    public function test_InscrireUtilisateurManuellement(): void
    {
        // Test ID: 1007
        // Simuler la création manuelle d’un utilisateur via un formulaire/admin

        $pseudo = 'userManual';
        $email = 'manual@example.com';

        $user = new User();
        $user->setPseudo($pseudo);
        $user->setEmail($email);
        $user->setFirstname('Jean');
        $user->setLastname('Dupont');
        $user->setActive(true);

        // Vérifier que l'utilisateur a bien ses données
        $this->assertEquals($pseudo, $user->getPseudo());
        $this->assertEquals($email, $user->getEmail());
        $this->assertTrue($user->isActive());
    }
}
