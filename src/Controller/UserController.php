<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
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
    public function logout():void{
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
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            } else {
                $entityManager->refresh($user);
            }
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['pseudo' => $user->getPseudo()]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $form->get('pseudo')->addError(new FormError('Ce pseudo est déjà utilisé.'));
            } else {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Profil mis à jour avec succès.');

                return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);

            }
        }

        return $this->render('user/edit_profile.html.twig', [
            'userForm' => $form->createView(),
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
