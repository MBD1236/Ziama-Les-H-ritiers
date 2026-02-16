<?php

namespace App\Controller;

use App\Entity\Bobine;
use App\Entity\MouvementStock;
use App\Entity\Produit;
use App\Form\BobineType;
use App\Form\ProduitType;
use App\Repository\MouvementStockRepository;
use App\Repository\ProduitRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_admin_produit_index', methods:['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produitRepository->paginateProduits($page)
        ]);
    }

    #[Route('/new', name: 'app_admin_produit_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Le produit a été bien enregistré.');
            return $this->redirectToRoute('app_admin_produit_index');
        }

        return $this->render('admin/produit/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_produit_edit', methods:['GET', 'POST'])]
    public function edit(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'Le produit a été bien modifié.');
            return $this->redirectToRoute('app_admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_produit_delete', methods: ['DELETE'])]
    public function delete(Produit $produit, MouvementStockRepository $mbr, EntityManagerInterface $em): Response
    {
        try {
            $mvts = $mbr->findBy(['produit' => $produit]);
            foreach ($mvts as $mvt) {
                $em->remove($mvt);
            }
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Le produit a bien été supprimé.');
            return $this->redirectToRoute('app_admin_produit_index');
        } catch (\Throwable $th) {
            $this->addFlash('danger', 'Erreur de suppression.');
            return $this->redirectToRoute('app_admin_produit_index');
        }
    }

    #[Route('/{id}/ajout-stock', name: 'app_admin_produit_ajout_stock', methods:['POST'])]
    public function ajoutStock(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        /* Je recupere les valeurs du formulaire */
        $quantite = (int)$request->request->get('quantite');
        $date = new DateTime();
        

        $mouvementProduit = new MouvementStock();
        $mouvementProduit->setQuantite($quantite);
        $mouvementProduit->setDate($date);
        $mouvementProduit->setTypeMouvement('entrée');
        $mouvementProduit->setProduit($produit);
        
        $quantiteInitiale = $produit->getQuantiteStock();
        $quantiteTotale = $quantiteInitiale + $quantite;
        $produit->setQuantiteStock($quantiteTotale);
        
        $em->persist($produit);
        $em->persist($mouvementProduit);
        $em->flush();
        $this->addFlash('success', 'Le stock a été bien augmenté.');

        return $this->redirectToRoute('app_admin_produit_index');
    }


    #[Route('/{id}/mouvement-produit', name: 'app_admin_produit_mouvement_produit', methods:['GET'])]
    public function voirMouvementStock(Request $request, Produit $produit, MouvementStockRepository $mouvementStockRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $mouvementsStock = $mouvementStockRepository->paginateMouvementsStock($produit, $page);
        return $this->render('admin/produit/mouvementStock.html.twig', [
            'mouvementsStock' => $mouvementsStock,
        ]);
    }

    #[Route('/search', name: 'app_admin_produit_search', methods:['GET'])]
    public function search(Request $request, ProduitRepository $produitRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $produits = $produitRepository->paginateProduitsWithSearch($query, $page);
        }else{
            $produits = [];
        }
        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produits,
            'query' => $query
        ]);
    }

    #[Route('/mouvement-produit/search', name: 'app_admin_mouvement_produit_search', methods:['GET'])]
    public function searchMouvement(Request $request, MouvementStockRepository $mouvementStockRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query or $query == '')
        {
            $mouvementsStock = $mouvementStockRepository->paginateMouvementBobineWithSearch($query, $page);
        }else{
            $mouvementsStock = [];
        }
        return $this->render('admin/produit/mouvementStock.html.twig', [
            'mouvementsStock' => $mouvementsStock
        ]);
    }
}
