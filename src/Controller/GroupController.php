<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GroupController extends AbstractController
{

///groups/create → Créer un groupe privé (4001)
///groups/{id} → Voir un groupe
///groups/manage → Ajouter/supprimer des membres

    #[Route('/group', name: 'app_group')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GroupController.php',
        ]);
    }
}
