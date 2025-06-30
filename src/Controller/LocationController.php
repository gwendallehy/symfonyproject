<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    #[Route('/locations', name: 'app_locations')]
    public function list(): Response
    {
        return $this->render('location/list.html.twig');
    }

    #[Route('/location/create', name: 'app_location_create')]
    public function create(): Response
    {
        return $this->render('location/form.html.twig');
    }

    #[Route('/cities/create', name: 'app_city_create')]
    public function createCity(): Response
    {
        return $this->render('location/form.html.twig');
    }
}
