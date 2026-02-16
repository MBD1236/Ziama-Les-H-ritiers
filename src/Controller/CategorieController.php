<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'app_admin_categorie_index', methods: ['GET'])]
    public function index(Request $request, CategorieRepository $categorieRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('admin/categorie/index.html.twig', [
            'categories' => $categorieRepository->paginateCategories($page)
        ]);
    }

    #[Route('/new', name: 'app_admin_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'La catégorie a été bien enregistrée.');
            return $this->redirectToRoute('app_admin_categorie_index');
        }

        return $this->render('admin/categorie/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Categorie $categorie, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'La catégorie a été bien modifiée.');
            return $this->redirectToRoute('app_admin_categorie_index');
        }

        return $this->render('admin/categorie/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_categorie_delete', methods: ['DELETE'])]
    public function delete(Categorie $categorie, ProduitRepository $produitRepository, EntityManagerInterface $em): Response
    {
        try {
            // Vérifier s'il y a des produits associés
            $produits = $produitRepository->findBy(['categorie' => $categorie]);
            
            if (!empty($produits)) {
                $this->addFlash('warning', 'Cette catégorie contient ' . count($produits) . ' produit(s). Veuillez les réassigner d\'abord.');
                return $this->redirectToRoute('app_admin_categorie_index');
            }

            $em->remove($categorie);
            $em->flush();
            $this->addFlash('success', 'La catégorie a bien été supprimée.');
            return $this->redirectToRoute('app_admin_categorie_index');
        } catch (\Throwable $th) {
            $this->addFlash('danger', 'Erreur lors de la suppression : ' . $th->getMessage());
            return $this->redirectToRoute('app_admin_categorie_index');
        }
    }

    #[Route('/search', name: 'app_admin_categorie_search', methods: ['GET'])]
    public function search(Request $request, CategorieRepository $categorieRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        
        if ($query)
        {
            $categories = $categorieRepository->paginateCategoriesWithSearch($query, $page);
        } else {
            $categories = [];
        }
        
        return $this->render('admin/categorie/index.html.twig', [
            'categories' => $categories,
            'query' => $query
        ]);
    }
}
