<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\UserTypeForm;
use App\Repository\UserRepository;
use App\Form\CsvImportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminPanelController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * US 1007 - Ajouter un utilisateur
     * En tant qu’admin, je peux créer un utilisateur manuellement via un formulaire.
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

            if (empty($plainPassword)) {
                $this->addFlash('error', 'Le mot de passe est requis.');
            } elseif ($plainPassword !== $confirmation) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                // Rôles et statut
                $user->setRoles(['ROLE_USER']);
                $user->setActive(true);
                $user->setAdministrator(false); // Par défaut

                // Upload photo (optionnel)
                $pictureFile = $form->get('picture')->getData();
                if ($pictureFile) {
                    $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                    $pictureFile->move($this->getParameter('pictures_directory'), $newFilename);
                    $user->setPicture($newFilename);
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
     * US 1008 - Désactiver un utilisateur
     * En tant qu’admin, je peux rendre inactif un utilisateur sélectionné.
     */
    #[Route('/admin/user/{id}/toggle-active', name: 'admin_user_toggle_active')]
    public function toggleActive(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setActive(!$user->isActive());
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            "L'utilisateur %s a été %s.",
            $user->getPseudo(),
            $user->isActive() ? 'réactivé' : 'désactivé'
        ));

        return $this->redirectToRoute('admin_users');
    }

    /**
     * US 1009 - Supprimer un utilisateur
     * En tant qu’admin, je peux supprimer un utilisateur sélectionné.
     */
    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST', 'GET'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur {$user->getPseudo()} a bien été supprimé.");

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/{id}/edit', name: 'admin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $confirmation = $form->get('confirmation')->getData();

            if ($plainPassword) {
                if ($plainPassword !== $confirmation) {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                    return $this->render('admin/user_form.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Upload de la photo (optionnel)
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $pictureFile->move($this->getParameter('pictures_directory'), $newFilename);
                $user->setPicture($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'editMode' => true,
        ]);
    }


    #[Route('/admin/user/import', name: 'admin_user_import')]
    public function importUsers(
        Request                     $request,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();

            if ($csvFile) {
                $filePath = $csvFile->getPathName();
                if (($handle = fopen($filePath, "r")) !== false) {
                    $header = null;
                    $createUsers = 0;
                    $errors = [];
                    $linenumber = 0;
                    $createdPseudos = [];

                    while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                        $linenumber++;

                        if (!$header) {
                            $header = $row;
                            continue;
                        }

                        if (count($header) !== count($row)) {
                            $errors[] = "Ligne $linenumber : colonnes attendues = " . count($header) . ", trouvées = " . count($row);
                            continue;
                        }

                        $data = array_combine($header, $row);

                        // Vérifie doublon pseudo dans CSV
                        if (in_array($data['pseudo'], $createdPseudos)) {
                            $errors[] = "Ligne $linenumber : le pseudo '{$data['pseudo']}' est déjà utilisé dans ce fichier.";
                            continue;
                        }

                        // Vérifie si email existe déjà en base
                        $existingMail = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
                        if ($existingMail) {
                            $errors[] = "Ligne $linenumber : l'utilisateur avec l'email : {$data['email']} existe déjà.";
                            continue;
                        }

                        // Vérifie si pseudo existe déjà en base
                        $existingPseudo = $entityManager->getRepository(User::class)->findOneBy(['pseudo' => $data['pseudo']]);
                        if ($existingPseudo) {
                            $errors[] = "Ligne $linenumber : l'utilisateur avec le pseudo : {$data['pseudo']} existe déjà.";
                            continue;
                        }

                        // Vérifie si site existe
                        $site = $entityManager->getRepository(Site::class)->findOneBy(['name' => $data['site']]);
                        if (!$site) {
                            $errors[] = "Ligne $linenumber : Le site {$data['site']} pour l'utilisateur {$data['pseudo']} n'existe pas.";
                            continue;
                        }

                    //Création  de l'utilisateur
                        $user = new User();
                        $user->setPseudo($data['pseudo']);
                        $user->setFirstname($data['firstname']);
                        $user->setLastname($data['lastname']);
                        $user->setEmail($data['email']);
                        $user->setPhone($data['phone']);
                        $user->setAdministrator(false);
                        $user->setActive(true);
                        $user->setSite($site);

                        // Force le rôle à "ROLE_USER"
                        $user->setRoles(['ROLE_USER']);

                        // Hachage du mot de passe en clair (depuis CSV)
                        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
                        $user->setPassword($hashedPassword);

                        //Importe picture si il existe
                        if (isset($data['picture'])) {
                            $user->setPicture('../images/default_profile.jpg');
                        }

                        $entityManager->persist($user);
                        $createUsers++;
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
        }

        return $this->render('admin/user_import.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/outing/{id}/cancel', name: 'admin_outing_cancel')]
    public function cancelOutingAsAdmin(int $id): Response
    {
        return $this->render('admin/cancel_outing.html.twig');
    }
}
