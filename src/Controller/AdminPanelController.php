<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminPanelController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/admin/user/create', name: 'admin_user_create')]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            //Définition des rôles....etc
            $user->setRoles(['ROLE_USER']);
            $user->setActive(true);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "L'utilisateur a bien été créé");
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/user/import', name: 'admin_user_import')]
    public function importUsers(): Response
    {
        return $this->render('admin/user_form.html.twig'); // peut changer
    }

    #[Route('/admin/outing/{id}/cancel', name: 'admin_outing_cancel')]
    public function cancelOutingAsAdmin(int $id): Response
    {
        return $this->render('admin/cancel_outing.html.twig');
    }
}
