<?php

namespace App\Controller;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use GroupType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/group')]
class GroupController extends AbstractController
{
    /**
     * Affiche la liste des groupes auxquels l'utilisateur appartient.
     */
    #[Route('/list', name: 'group_list')]
    public function list(GroupRepository $groupRepository): Response
    {
        $user = $this->getUser();
        $groups = $groupRepository->findByOwner($user);

        return $this->render('group/list.html.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * Crée un nouveau groupe avec le formulaire.
     * L'utilisateur connecté devient automatiquement le propriétaire.
     */
    #[Route('/create', name: 'group_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $group = new Group();
        $group->setOwner($this->getUser());

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'Groupe créé avec succès.');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet d’éditer un groupe existant.
     * Seul le propriétaire du groupe peut effectuer cette action.
     */
    #[Route('/edit/{id}', name: 'group_edit')]
    public function edit(Group $group, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifie si l'utilisateur est bien le propriétaire du groupe
        if ($group->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce groupe.');
        }

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Groupe modifié.');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('group/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }

    /**
     * Supprime un groupe via POST sécurisé par token CSRF.
     * Seul le propriétaire peut le supprimer.
     */
    #[Route('/delete/{id}', name: 'group_delete', methods: ['POST'])]
    public function delete(Group $group, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifie si l'utilisateur est bien le propriétaire du groupe
        if ($group->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce groupe.');
        }

        // Vérifie le token CSRF pour prévenir les suppressions non autorisées
        if ($this->isCsrfTokenValid('delete_group_' . $group->getId(), $request->request->get('_token'))) {
            $em->remove($group);
            $em->flush();

            $this->addFlash('success', 'Groupe supprimé.');
        }

        return $this->redirectToRoute('group_list');
    }
}
