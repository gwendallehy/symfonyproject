<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class AdminPanelController extends AbstractController
{

///admin/users → Gérer les utilisateurs
///admin/outing/cancel/{id} → Annuler une sortie (2012)
///admin/groups → Voir et gérer groupes privés
///admin/dashboard → Tableau de bord administrateur

    #[Route('/admin/panel', name: 'app_admin_panel')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AdminPanelController.php',
        ]);
    }
}
