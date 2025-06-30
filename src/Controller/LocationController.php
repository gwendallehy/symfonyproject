<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LocationController extends AbstractController
{
//
///location/cities → Ajouter une ville (2013)
///location/places → Ajouter un lieu (2014)
///
    #[Route('/location', name: 'app_location')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LocationController.php',
        ]);
    }
}
