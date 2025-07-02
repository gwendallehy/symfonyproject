<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/groups', name: 'app_groups')]
    public function list(): Response
    {
        return $this->render('group/index.html.twig');
    }

    #[Route('/group/create', name: 'app_group_create')]
    public function create(): Response
    {
        return $this->render('group/unified_form.html.twig');
    }

    #[Route('/group/{id}', name: 'app_group_show')]
    public function show(int $id): Response
    {
        return $this->render('group/show.html.twig');
    }
}
