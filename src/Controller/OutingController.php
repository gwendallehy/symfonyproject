<?php

namespace App\Controller;

use App\Entity\Outgoing;
use App\Form\OutingFilterType;
use App\Form\OutingTypeForm;
use App\Repository\EtatRepository;
use App\Repository\OutgoingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;


class OutingController extends AbstractController
{
    #[Route('/', name: 'app_outing_list')]
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
    public function create(
        Security $security,
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ): Response {
        $user = $security->getUser();

        $outing = new Outgoing();
        $outing->setOrganizer($user);
        $outing->setSite($user->getSite());

        $form = $this->createForm(OutingTypeForm::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
             $outing->setEtat($etat);

            $entityManager->persist($outing);
            $entityManager->flush();

            return $this->redirectToRoute('app_outing_list');
        }

        return $this->render('outing/form.html.twig', [
            'outingForm' => $form->createView(),
        ]);
    }

    #[Route('/outing/{id}', name: 'app_outing_show')]
    public function show(int $id, OutgoingRepository $outgoingRepository): Response
    {
        $outing = $outgoingRepository->find($id);

        if (!$outing) {
            throw new NotFoundHttpException("Sortie non trouvée.");
        }

        return $this->render('outing/show.html.twig', [
            'outing' => $outing,
        ]);
    }

    #[Route('/outing/{id}/edit', name: 'app_outing_edit')]
    public function edit(
        int $id,
        Request $request,
        OutgoingRepository $outgoingRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $outing = $outgoingRepository->find($id);

        if (!$outing) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        $user = $security->getUser();
        if ($outing->getOrganizer() !== $user) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette sortie.");
        }

        $form = $this->createForm(OutingTypeForm::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_outing_show', ['id' => $outing->getId()]);
        }

        return $this->render('outing/form.html.twig', [
            'outingForm' => $form->createView(),
        ]);
    }

    #[Route('/outing/{id}/cancel', name: 'app_outing_cancel')]
    public function cancel(
        int $id,
        Request $request,
        OutgoingRepository $outgoingRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $outing = $outgoingRepository->find($id);
        if (!$outing) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        $user = $security->getUser();
        if ($outing->getOrganizer() !== $user) {
            throw $this->createAccessDeniedException('Seul l’organisateur peut annuler cette sortie.');
        }

        if ($request->isMethod('POST')) {
            $reason = $request->request->get('reason');
            $etatAnnulee = $etatRepository->findOneBy(['libelle' => 'Annulée']);
            $outing->setEtat($etatAnnulee);
            $outing->setDescription($outing->getDescription() . "\n\n[ANNULATION] " . $reason);

            $entityManager->flush();

            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
        ]);
    }

    #[Route('/outings/archive', name: 'app_outing_archive')]
    public function archive(OutgoingRepository $outgoingRepository): Response
    {
        $pastOutings = $outgoingRepository->createQueryBuilder('o')
            ->where('o.dateBegin < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('o.dateBegin', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('outing/archive.html.twig', [
            'outings' => $pastOutings,
        ]);
    }
}
