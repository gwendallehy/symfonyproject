<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('user/login.html.twig');
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    #[Route('/profile/edit', name: 'app_user_profile_edit')]
    public function editProfile(): Response
    {
        return $this->render('user/edit_profile.html.twig');
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
