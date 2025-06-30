<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class OutingController extends AbstractController
{
///outings → Liste des sorties (2001)
///outing/create → Création de sortie (2002)
///outing/register/{id} → Inscription à une sortie (2003)
///outing/unregister/{id} → Désistement (2004)
///outing/cancel/{id} → Annulation par l'organisateur (2006)
///outing/archive → Archive des sorties (2007)
///outing/participants/{id} → Voir participants + profil (2008)
///
    #[Route('/outing', name: 'app_outing')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/OutingController.php',
        ]);
    }


}
