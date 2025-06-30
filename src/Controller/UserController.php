<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{

///user/login → Connexion (1001)
///user/logout → Déconnexion
///user/profile → Voir/modifier son profil (1003)
///user/photo/upload → Upload photo de profil (1004)
///user/password/reset → Réinitialisation mot de passe (1005)
///user/register → Inscription manuelle (1007)
///user/bulk-register → Import CSV (1006)
///user/deactivate → Désactiver utilisateurs (1008)
///user/delete → Supprimer utilisateurs (1009)

    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }


}
