<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserTypeForm;
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
    public function importUsers(): Response
    {
        return $this->render('admin/user_form.html.twig');
    }

    #[Route('/admin/outing/{id}/cancel', name: 'admin_outing_cancel')]
    public function cancelOutingAsAdmin(int $id): Response
    {
        return $this->render('admin/cancel_outing.html.twig');
    }
}
