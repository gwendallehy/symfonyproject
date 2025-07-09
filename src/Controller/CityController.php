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
    /**
     * API - Crée une ville si elle n'existe pas déjà, sinon la retourne
     *
     * Cette route accepte une requête POST avec un nom de ville et un code postal.
     * Si une ville avec ces paramètres existe déjà, elle est retournée.
     * Sinon, une nouvelle ville est créée, enregistrée, puis retournée.
     *
     * Exemple de body JSON attendu :
     * {
     *   "name": "Paris",
     *   "postalCode": "75000"
     * }
     *
     * @param Request $request Requête HTTP contenant les données JSON
     * @param CityRepository $cityRepo Repository pour rechercher les villes
     * @param EntityManagerInterface $em Permet la persistance des entités
     *
     * @return JsonResponse JSON contenant les informations de la ville
     */
    #[Route('/api/city', name: 'api_create_or_fetch_city', methods: ['POST'])]
    public function createOrFetchCity(
        Request $request,
        CityRepository $cityRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        // Décoder le JSON envoyé dans le corps de la requête
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $postalCode = $data['postalCode'] ?? null;

        // Vérifier la présence des champs requis
        if (!$name || !$postalCode) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        // Rechercher la ville en base de données
        $city = $cityRepo->findOneBy([
            'name' => $name,
            'postalCode' => $postalCode
        ]);

        // Si elle n'existe pas, on la crée et on la persiste
        if (!$city) {
            $city = new City();
            $city->setName($name);
            $city->setPostalCode($postalCode);

            $em->persist($city);
            $em->flush();
        }

        // Réponse JSON contenant les informations de la ville (existante ou nouvellement créée)
        return new JsonResponse([
            'id' => $city->getId(),
            'name' => $city->getName(),
            'postalCode' => $city->getPostalCode(),
        ]);
    }
}
