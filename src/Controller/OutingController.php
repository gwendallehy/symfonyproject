<?php

namespace App\Controller;

use App\Entity\Outgoing;
use App\Entity\Place;
use App\Form\OutingFilterTypeForm;
use App\Form\OutingTypeForm;
use App\Repository\EtatRepository;
use App\Repository\OutgoingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
class OutingController extends AbstractController
{
    /**
     * US 2001 - Afficher les sorties par site
     * En tant que participant, je peux lister les sorties publiées sur chaque site,
     * celles auxquelles je suis inscrit et celles dont je suis l’organisateur.
     * Je peux filtrer cette liste suivant différents critères.
     */
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'app_outing_list')]
    public function list(
        Security $security,
        OutgoingRepository $outgoingRepository,
        Request $request,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $security->getUser();
        $formOptions = [];

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            $isMobile = false;
        } else {
            $ua = $request->headers->get('User-Agent', '');
            $isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua) === 1;
        }

        if ($isMobile) {
            $formOptions['data'] = [
                'subscribed' => true,
            ];
            $isMobile = true;
        } else {
            $isMobile = false;
        }

        $form = $this->createForm(OutingFilterTypeForm::class, null, $formOptions);
        $form->handleRequest($request);
        $filters = $isMobile ? ['site' => $user->getSite()] : $form->getData();

        $outings = $outgoingRepository->findFilteredOutings($user, $filters ?? []);
        foreach ($outings as $outing) {
            $outing->updateEtat($etatRepository);
        }
        $entityManager->flush();

        return $this->render('outing/list.html.twig', [
            'outings' => $outings,
            'filterForm' => $form->createView(),
            'user' => $user,
            'isMobile' => $isMobile,
        ]);
    }

    /**
     * US 2002 - Créer une sortie
     * En tant qu'organisateur, je peux créer une nouvelle sortie.
     */
    #[Route('/outing/create', name: 'app_outing_create')]
    public function create(
        Security $security,
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        SessionInterface $session
    ): Response {

        $user = $security->getUser();

        $outing = new Outgoing();
        $outing->setOrganizer($user);
        $outing->setSite($user->getSite());


        $newPlaceId = $session->get('new_place_id');
        if ($newPlaceId) {
            $newPlace = $entityManager->getRepository(Place::class)->find($newPlaceId);
            if ($newPlace) {
                $outing->setPlace($newPlace);
            }
            $session->remove('new_place_id');
        }


        $form = $this->createForm(OutingTypeForm::class, $outing);
        // 🟡 Intercepter le clic sur "Ajouter un lieu" AVANT validation du formulaire
        if ($request->isMethod('POST') && $request->request->has('add_place')) {
            return $this->redirectToRoute('location_create', [
                'type' => 'place',
                'returnUrl' => $request->getUri(),
            ]);
        }
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

    /**
     * Détail d'une sortie
     */

    #[Route('/outing/{id}', name: 'app_outing_show', requirements: ['id' => '\d+'])]
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

    /**
     * Éditer une sortie (réservé à l'organisateur)
     */

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

        if ($outing->hasStarted()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier une sortie déjà commencée.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        $user = $security->getUser();
        if ($outing->getOrganizer() !== $user && !$user->isAdministrator()) {
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

    /**
     * US 2006 - Annuler une sortie
     * En tant qu'organisateur, je peux annuler une sortie non commencée.
     */
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

        if ($outing->hasStarted()) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler une sortie déjà commencée.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }


        $user = $security->getUser();
        if ($outing->getOrganizer() !== $user && !$user->isAdministrator()) {
            throw $this->createAccessDeniedException('Seul l’organisateur ou un administrateur peut annuler cette sortie.');
        }


        if ($request->isMethod('POST')) {
            $reason = $request->request->get('reason');
            $etatAnnulee = $etatRepository->findOneBy(['libelle' => 'Annulée']);
            $outing->setEtat($etatAnnulee);
            $outing->setDescription($outing->getDescription() . "\n\n[ANNULATION] " . $reason);
            $entityManager->flush();

            return $this->redirectToRoute('app_outing_list', ['id' => $id]);
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
        ]);
    }


    #[Route('/outings/archive', name: 'app_outing_archive')]
    public function archive(OutgoingRepository $outgoingRepository): Response
    {
        $pastOutings = $outgoingRepository->createQueryBuilder('o')
            ->andWhere('o.dateBegin < :dateLimit')
            ->setParameter('dateLimit', (new \DateTime())->modify('-1 month'))
            ->orderBy('o.dateBegin', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('outing/archive.html.twig', [
            'outings' => $pastOutings,
        ]);
    }

    /**
     * US 2003 / US 2005 - S'inscrire à une sortie
     * En tant que participant, je peux m’inscrire si la sortie est publiée
     * et que la date limite d’inscription n’est pas dépassée.
     */
    #[Route('/outing/{id}/subscribe', name: 'app_outing_subscribe')]
    public function subscribe(
        int $id,
        OutgoingRepository $outgoingRepository,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $security->getUser();
        $outing = $outgoingRepository->find($id);

        if (!$outing || !$user) {
            throw $this->createNotFoundException('Sortie ou utilisateur non trouvé.');
        }

        if ($outing->getEtat()->getLibelle() === 'Annulée') {
            $this->addFlash('error', 'Impossible de s’inscrire : sortie annulée.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        if ($outing->isFull()) {
            $this->addFlash('error', 'La sortie est complète.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }


        if (!$outing->isOpenForSubscription()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à cette sortie.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        if ($outing->getParticipants()->contains($user)) {
            $this->addFlash('info', 'Vous êtes déjà inscrit.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        $outing->addParticipant($user);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription réussie.');
        return $this->redirectToRoute('app_outing_show', ['id' => $id]);
    }

    /**
     * US 2004 - Se désister
     * Un participant peut se désister avant le début de la sortie.
     */

    #[Route('/outing/{id}/unsubscribe', name: 'app_outing_unsubscribe')]
    public function unsubscribe(
        int $id,
        OutgoingRepository $outgoingRepository,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $security->getUser();
        $outing = $outgoingRepository->find($id);

        if (!$outing || !$user) {
            throw $this->createNotFoundException('Sortie ou utilisateur non trouvé.');
        }

        if ($outing->hasStarted()) {
            $this->addFlash('error', 'Vous ne pouvez plus vous désister : la sortie a commencé.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        if ($outing->getOrganizer() === $user) {
            $this->addFlash('error', 'L’organisateur ne peut pas se désister de sa propre sortie.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        if (!$outing->getParticipants()->contains($user)) {
            $this->addFlash('info', 'Vous n’êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('app_outing_show', ['id' => $id]);
        }

        $outing->removeParticipant($user);
        $entityManager->flush();

        $this->addFlash('success', 'Vous vous êtes désisté.');
        return $this->redirectToRoute('app_outing_show', ['id' => $id]);
    }
}
