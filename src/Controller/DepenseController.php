<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\Depense;
use App\Form\DepenseType;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/depense')]
class DepenseController extends AbstractController
{
    #[Route('/', name: 'app_admin_depense_index', methods:['GET'])]
    public function index(Request $request, DepenseRepository $depenseRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/depense/index.html.twig', [
            'depenses' => $depenseRepository->paginateDepenses($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_depense_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $depense = new Depense();
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isSubmitted())
        {
            /* Enregistrer le montantRegle dans la caisse */
            $caisse = new Caisse();
            $caisse->setDate($depense->getDateDepense());
            $caisse->setType('Décaissement');
            $caisse->setMontant($depense->getMontant());
            $caisse->setDescription('Depense');

            $em->persist($depense);
            $em->persist($caisse);
            $em->flush();

            $this->addFlash('success', 'La depense à bien été enregistré.');
            return $this->redirectToRoute('app_admin_depense_index');
        }
        return $this->render('admin/depense/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_depense_edit', methods:['GET', 'POST'])]
    public function edit(Depense $depense, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isSubmitted())
        {
            $em->flush();
            $this->addFlash('success', 'La depense à bien été enregistré.');
            return $this->redirectToRoute('app_admin_depense_index');
        }
        return $this->render('admin/depense/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/search', name: 'app_admin_depense_search', methods:['GET'])]
    public function search(Request $request, DepenseRepository $depenseRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);

        if ($query)
        {
            $depenses = $depenseRepository->paginateDepensesWithSearch($query, $page);
        }else{
            $depenses = [];
        }
        return $this->render('admin/depense/index.html.twig', [
            'depenses' => $depenses,
            'query' => $query
        ]);
    }

}
