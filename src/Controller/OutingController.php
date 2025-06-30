<?php

namespace App\Controller;

use App\Form\OutingFilterType;
use App\Repository\OutgoingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;


class OutingController extends AbstractController
{
    #[Route('/outings', name: 'app_outing_list')]
    public function list(
        Security $security,
        OutgoingRepository $outgoingRepository,
        Request $request
    ): Response {
        $user = $security->getUser();

        $form = $this->createForm(OutingFilterType::class);
        $form->handleRequest($request);
        $filters = $form->getData();
        $outings = $outgoingRepository->findFilteredOutings($user, $filters ?? []);
        return $this->render('outing/list.html.twig', [
            'outings' => $outings,
            'filterForm' => $form->createView(),
        ]);
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
