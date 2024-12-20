<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\Fournisseur;
use App\Entity\TransactionFournisseur;
use App\Form\FournisseurType;
use App\Form\TransactionFournisseurType;
use App\Repository\FournisseurRepository;
use App\Repository\TransactionFournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/fournisseur')]
class FournisseurController extends AbstractController
{
    #[Route('/', name: 'app_admin_fournisseur_index', methods:['GET'])]
    public function index(Request $request, FournisseurRepository $fournisseurRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/fournisseur/index.html.twig', [
            'fournisseurs' => $fournisseurRepository->paginateFournisseurs($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_fournisseur_new', methods:['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $fournisseur = new Fournisseur();
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($fournisseur);
            $em->flush();
            $this->addFlash('success', 'Le fournisseur a bien été enregistré.');
            return $this->redirectToRoute('app_admin_fournisseur_index');
        }

        return $this->render('admin/fournisseur/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/new', name: 'app_admin_fournisseur_edit', methods:['GET','POST'])]
    public function edit(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        $fournisseur = new Fournisseur();
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'Le fournisseur a bien été modifié.');
            return $this->redirectToRoute('app_admin_fournisseur_index');
        }

        return $this->render('admin/fournisseur/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_fournisseur_delete', methods: ['DELETE'])]
    public function delete(Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        $em->remove($fournisseur);
        $em->flush();
        $this->addFlash('success', 'Le fournisseur a bien été supprimé.');
        return $this->redirectToRoute('app_admin_fournisseur_index');
    }

    #[Route('/search', name: 'app_admin_fournisseur_search', methods:['GET'])]
    public function search(Request $request, FournisseurRepository $fournisseurRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $fournisseurs = $fournisseurRepository->paginateFournisseursWithSearch($query, $page);
        }else{
            $fournisseurs = [];
        }
        return $this->render('admin/fournisseur/index.html.twig', [
            'fournisseurs' => $fournisseurs,
            'query' => $query
        ]);
    }

}
