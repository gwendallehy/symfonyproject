<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    #[Route('/outings', name: 'app_outing_list')]
    public function list(): Response
    {
        return $this->render('outing/list.html.twig');
    }

    #[Route('/outing/create', name: 'app_outing_create')]
    public function create(): Response
    {
        return $this->render('outing/form.html.twig');
    }

    #[Route('/outing/{id}', name: 'app_outing_show')]
    public function show(int $id): Response
    {
        return $this->render('outing/show.html.twig');
    }

    #[Route('/outing/{id}/edit', name: 'app_outing_edit')]
    public function edit(int $id): Response
    {
        return $this->render('outing/form.html.twig');
    }

    #[Route('/outing/{id}/cancel', name: 'app_outing_cancel')]
    public function cancel(int $id): Response
    {
        return $this->render('outing/cancel.html.twig');
    }

    #[Route('/outings/archive', name: 'app_outing_archive')]
    public function archive(): Response
    {
        return $this->render('outing/archive.html.twig');
    }
}
