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
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('user/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception("Ne pas oublier d'activer logout dans security.yaml");
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }

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

        $form = $this->createForm(UserTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification mot de passe + confirmation
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

            // Upload de la photo si fournie
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                    $user->setPicture('pictures/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Erreur lors de l'upload de la photo");
                }
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
        ]);
    }


    #[Route('/profile/photo', name: 'app_user_photo_upload')]
    public function uploadPhoto(): Response
    {
        return $this->render('user/photo_upload.html.twig');
    }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(): Response
    {
        return $this->render('user/reset_password.html.twig');
    }
}
