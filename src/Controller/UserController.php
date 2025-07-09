<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserTypeForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * US 1001 - Se connecter
     * En tant que participant, je peux me connecter avec mon identifiant et mot de passe.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('user/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
    /**
     * US 1001 (suite) - Se déconnecter
     * En tant que participant, je peux me déconnecter de mon compte.
     */

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception("Ne pas oublier d'activer logout dans security.yaml");
    }

    /**
     * US 2008 - Afficher le profil d’un autre participant
     * En tant que participant, je peux consulter le profil public d’un autre utilisateur depuis la fiche sortie.
     */

    #[Route('/profile/{id}', name: 'app_user_profile', requirements: ['id' => '\d+'])]
    public function profile(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('user/profile.html.twig', [
            'userProfile' => $user,
        ]);
    }

    /**
     * @throws ORMException
     */
    /**
     * US 1003 - Modifier son profil
     * En tant que participant connecté, je peux modifier mes informations personnelles (nom, pseudo, téléphone, etc.).
     *
     * US 1004 - Ajouter une photo de profil
     * En tant que participant, je peux téléverser une photo de profil.
     */
    #[Route('/profile/edit', name: 'app_user_profile_edit')]
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        $originPicture = $user->getPicture();

        if ($originPicture === null || $originPicture === '') {
            $originPicture = 'images/default_profile.jpg';
            $user->setPicture($originPicture);
        }
        $form = $this->createForm(UserTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $confirmation = $form->get('confirmation')->getData();

            if ($password) {
                if ($password !== $confirmation) {
                    $form->get('confirmation')->addError(new FormError('La confirmation ne correspond pas au mot de passe.'));
                } else {
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
            }
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate(
                    'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                    $originalFilename
                );
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                    $user->setPicture('pictures/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Erreur lors de l'upload de la photo.");
                }
            }else{
                $user->setPicture($originPicture ?: 'images/default_profile.jpg');
            }

            // Vérification du pseudo unique
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['pseudo' => $user->getPseudo()]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $form->get('pseudo')->addError(new FormError('Ce pseudo est déjà utilisé.'));
            }

            // Si aucune erreur sur le formulaire (y compris la confirmation mot de passe)
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Profil mis à jour avec succès.');

                return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);
            }
        }

        return $this->render('user/edit_profile.html.twig', [
            'userForm' => $form->createView(),
            'userProfile' => $user,
            'originPicture' => $originPicture,
        ]);
    }

    /**
     * US 1005 - Mot de passe oublié
     * En tant qu'utilisateur, je peux accéder à la page de réinitialisation de mot de passe.
     * ⚠️ À compléter : traitement + envoi du mail de réinitialisation.
     */
    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(): Response
    {
        return $this->render('user/reset_password.html.twig');
    }
}
