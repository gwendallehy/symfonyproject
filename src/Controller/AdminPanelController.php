<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminPanelController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/admin/user/create', name: 'admin_user_create')]
    public function createUser(): Response
    {
        return $this->render('admin/user_form.html.twig');
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
