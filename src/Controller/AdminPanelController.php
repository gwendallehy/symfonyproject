<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\UserTypeForm;
use App\Form\CsvImportType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminPanelController extends AbstractController
{
    /**
     * Liste tous les utilisateurs.
     */
    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * US 1007 - Créer un nouvel utilisateur manuellement via un formulaire.
     */
    #[Route('/admin/user/create', name: 'admin_user_create')]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $confirmation = $form->get('confirmation')->getData();

            // Vérifie que le mot de passe est présent et confirmé
            if (empty($plainPassword)) {
                $this->addFlash('error', 'Le mot de passe est requis.');
            } elseif ($plainPassword !== $confirmation) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } else {
                // Hash du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                // Attributs par défaut
                $user->setRoles(['ROLE_USER']);
                $user->setActive(true);
                $user->setAdministrator(false);

                // Gestion de la photo de profil
                $pictureFile = $form->get('picture')->getData();
                if ($pictureFile) {
                    $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                    $pictureFile->move($this->getParameter('pictures_directory'), $newFilename);
                    $user->setPicture('pictures/' . $newFilename);
                } else {
                    $user->setPicture('default_profile.jpg');
                }

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash("success", "L'utilisateur a bien été créé.");
                return $this->redirectToRoute('admin_users');
            }
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * US 1008 - Activer/désactiver un utilisateur.
     */
    #[Route('/admin/user/{id}/toggle-active', name: 'admin_user_toggle_active')]
    public function toggleActive(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setActive(!$user->isActive());
        $entityManager->flush();

        $status = $user->isActive() ? 'réactivé' : 'désactivé';
        $this->addFlash('success', "L'utilisateur {$user->getPseudo()} a été $status.");

        return $this->redirectToRoute('admin_users');
    }

    /**
     * US 1009 - Supprimer un utilisateur.
     */
    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST', 'GET'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        // Désinscription de toutes les sorties
        foreach ($user->getOutings() as $outing) {
            $outing->removeParticipant($user);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur {$user->getPseudo()} a bien été supprimé.");

        return $this->redirectToRoute('admin_users');
    }

    /**
     * Modifier un utilisateur.
     */
    #[Route('/admin/user/{id}/edit', name: 'admin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $originPicture = $user->getPicture() ?: 'images/default_profile.jpg';
        $user->setPicture($originPicture);

        $form = $this->createForm(UserTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $confirmation = $form->get('confirmation')->getData();

            // Vérifie le mot de passe si fourni
            if ($plainPassword) {
                if ($plainPassword !== $confirmation) {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                    return $this->render('user/edit_profile.html.twig', [
                        'userForm' => $form->createView(),
                        'editMode' => true,
                        'userProfile' => $user,
                        'originPicture' => $originPicture,
                    ]);
                }

                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Gestion de la photo
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $safeFilename = transliterator_transliterate(
                    'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                    pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME)
                );
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move($this->getParameter('pictures_directory'), $newFilename);
                    $user->setPicture('pictures/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Erreur lors de l'upload de la photo.");
                }
            } else {
                $user->setPicture($originPicture);
            }

            // Vérifie unicité du pseudo
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['pseudo' => $user->getPseudo()]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $form->get('pseudo')->addError(new FormError('Ce pseudo est déjà utilisé.'));
                return $this->render('user/edit_profile.html.twig', [
                    'userForm' => $form->createView(),
                    'editMode' => true,
                    'userProfile' => $user,
                    'originPicture' => $originPicture,
                ]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('user/edit_profile.html.twig', [
            'userForm' => $form->createView(),
            'editMode' => true,
            'userProfile' => $user,
            'originPicture' => $originPicture,
        ]);
    }

    /**
     * Import d'utilisateurs via un fichier CSV.
     */
    #[Route('/admin/user/import', name: 'admin_user_import')]
    public function importUsers(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();

            if ($csvFile && ($handle = fopen($csvFile->getPathName(), "r")) !== false) {
                $header = null;
                $createUsers = 0;
                $errors = [];
                $linenumber = 0;
                $createdPseudos = [];

                while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                    $linenumber++;

                    // Stockage des en-têtes
                    if (!$header) {
                        $header = $row;
                        continue;
                    }

                    // Vérifie que la ligne correspond au format attendu
                    if (count($header) !== count($row)) {
                        $errors[] = "Ligne $linenumber : nombre de colonnes incorrect.";
                        continue;
                    }

                    $data = array_combine($header, $row);

                    // Vérifie doublons dans le fichier
                    if (in_array($data['pseudo'], $createdPseudos)) {
                        $errors[] = "Ligne $linenumber : pseudo '{$data['pseudo']}' déjà présent.";
                        continue;
                    }

                    // Vérifie unicité email et pseudo
                    if ($entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
                        $errors[] = "Ligne $linenumber : email '{$data['email']}' déjà existant.";
                        continue;
                    }

                    if ($entityManager->getRepository(User::class)->findOneBy(['pseudo' => $data['pseudo']])) {
                        $errors[] = "Ligne $linenumber : pseudo '{$data['pseudo']}' déjà existant.";
                        continue;
                    }

                    // Vérifie que le site existe
                    $site = $entityManager->getRepository(Site::class)->findOneBy(['name' => $data['site']]);
                    if (!$site) {
                        $errors[] = "Ligne $linenumber : site '{$data['site']}' introuvable.";
                        continue;
                    }

                    // Création de l'utilisateur
                    $user = new User();
                    $user->setPseudo($data['pseudo']);
                    $user->setFirstname($data['firstname']);
                    $user->setLastname($data['lastname']);
                    $user->setEmail($data['email']);
                    $user->setPhone($data['phone']);
                    $user->setAdministrator(false);
                    $user->setActive(true);
                    $user->setSite($site);
                    $user->setRoles(['ROLE_USER']);
                    $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

                    // Image par défaut
                    $user->setPicture('../images/default_profile.jpg');

                    $entityManager->persist($user);
                    $createUsers++;
                    $createdPseudos[] = $data['pseudo'];
                }

                fclose($handle);
                $entityManager->flush();

                $this->addFlash("success", "$createUsers utilisateur(s) importé(s) avec succès.");
                foreach ($errors as $err) {
                    $this->addFlash("warning", $err);
                }

                return $this->redirectToRoute('admin_users');
            }
        }

        return $this->render('admin/user_import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Annulation de sortie en tant qu’admin (en cours de développement).
     */
    #[Route('/admin/outing/{id}/cancel', name: 'admin_outing_cancel')]
    public function cancelOutingAsAdmin(int $id): Response
    {
        return $this->render('admin/cancel_outing.html.twig');
    }
}
