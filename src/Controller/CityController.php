<?php
// src/Controller/CityController.php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CityController extends AbstractController
{
    #[Route('/api/city', name: 'api_create_or_fetch_city', methods: ['POST'])]
    public function createOrFetchCity(Request $request, CityRepository $cityRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $postalCode = $data['postalCode'] ?? null;

        if (!$name || !$postalCode) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        // Vérifier si la ville existe déjà
        $city = $cityRepo->findOneBy(['name' => $name, 'postalCode' => $postalCode]);

        if (!$city) {
            // Créer et enregistrer la ville
            $city = new City();
            $city->setName($name);
            $city->setPostalCode($postalCode);
            $em->persist($city);
            $em->flush();
        }

        return new JsonResponse([
            'id' => $city->getId(),
            'name' => $city->getName(),
            'postalCode' => $city->getPostalCode(),
        ]);
    }
}
