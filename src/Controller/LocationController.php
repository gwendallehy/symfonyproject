<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Place;
use App\Entity\Site;
use App\Form\CityForm;
use App\Form\PlaceType;
use App\Form\SiteForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    #[Route('/location/{type}', name: 'location_list')]
    public function listEntities(string $type, EntityManagerInterface $em): Response
    {
        switch ($type) {
            case 'place':
                $entities = $em->getRepository(Place::class)->findAll();
                $title = "Lieux";
                break;
            case 'site':
                $entities = $em->getRepository(Site::class)->findAll();
                $title = "Sites";
                break;
            case 'city':
                $entities = $em->getRepository(City::class)->findAll();
                $title = "Villes";
                break;
            default:
                throw $this->createNotFoundException("Type inconnu.");
        }

        return $this->render('location/unified_list.html.twig', [
            'entities' => $entities,
            'type' => $type,
            'title' => $title,
        ]);
    }
    #[Route('/location/{type}/create', name: 'location_create')]
    public function createEntity(string $type, Request $request, EntityManagerInterface $em): Response
    {
        switch ($type) {
            case 'place':
                $entity = new Place();
                $form = $this->createForm(PlaceType::class, $entity);
                break;
            case 'site':
                $entity = new Site();
                $form = $this->createForm(SiteForm::class, $entity);
                break;
            case 'city':
                $entity = new City();
                $form = $this->createForm(CityForm::class, $entity);
                break;
            default:
                throw $this->createNotFoundException("Type inconnu.");
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', ucfirst($type).' créé avec succès.');
            return $this->redirectToRoute('location_list', ['type' => $type]);
        }

        return $this->render('location/unified_form.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }
    #[Route('/admin/location/{type}/{id}/edit', name: 'admin_location_edit')]
    public function editEntity(
        string $type,
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        switch ($type) {
            case 'place':
                $entity = $em->getRepository(Place::class)->find($id);
                $form = $this->createForm(PlaceType::class, $entity);
                break;
            case 'site':
                $entity = $em->getRepository(Site::class)->find($id);
                $form = $this->createForm(SiteForm::class, $entity);
                break;
            case 'city':
                $entity = $em->getRepository(City::class)->find($id);
                $form = $this->createForm(CityForm::class, $entity);
                break;
            default:
                throw $this->createNotFoundException("Type d'entité invalide.");
        }

        if (!$entity) {
            throw $this->createNotFoundException(ucfirst($type)." introuvable.");
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', ucfirst($type).' modifié avec succès.');
            return $this->redirectToRoute('location_list', ['type' => $type]);
        }

        return $this->render('location/unified_form.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'editMode' => true,
        ]);
    }
    #[Route('/admin/location/{type}/{id}/delete', name: 'admin_location_delete')]
    public function deleteEntity(
        string $type,
        int $id,
        EntityManagerInterface $em
    ): Response {
        switch ($type) {
            case 'place':
                $entity = $em->getRepository(Place::class)->find($id);
                break;
            case 'site':
                $entity = $em->getRepository(Site::class)->find($id);
                break;
            case 'city':
                $entity = $em->getRepository(City::class)->find($id);
                break;
            default:
                throw $this->createNotFoundException("Type d'entité invalide.");
        }

        if (!$entity) {
            throw $this->createNotFoundException(ucfirst($type)." introuvable.");
        }

        $em->remove($entity);
        $em->flush();

        $this->addFlash('success', ucfirst($type).' supprimé avec succès.');
        return $this->redirectToRoute('location_list', ['type' => $type]);
    }

}
