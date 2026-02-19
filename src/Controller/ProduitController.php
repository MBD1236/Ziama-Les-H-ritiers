<?php

namespace App\Controller;

use App\Entity\Bobine;
use App\Entity\Caisse;
use App\Entity\Depense;
use App\Entity\MouvementStock;
use App\Entity\Produit;
use App\Form\BobineType;
use App\Form\ProduitType;
use App\Repository\CaisseRepository;
use App\Repository\LigneVenteRepository;
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
    #[Route('/', name: 'app_admin_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produitRepository->paginateProduits($page)
        ]);
    }

    #[Route('/new', name: 'app_admin_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CaisseRepository $caisseRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        $depense = new Depense();
        $sommeCaisse = $caisseRepository->getEtatCaisse();


        if ($form->isSubmitted() && $form->isValid()) {
            $quantite = $produit->getQuantiteStock();
            $montant = $produit->getPrixAchat() * $quantite;
            $date = new DateTime();
            if ($montant > $sommeCaisse) {
                $this->addFlash('danger', 'Solde insuffisant.');
                return $this->redirectToRoute('app_admin_produit_index');
            }
            $mouvementProduit = new MouvementStock();
            $mouvementProduit->setQuantite($quantite);
            $mouvementProduit->setDate($date);
            $mouvementProduit->setTypeMouvement('Prémière entrée du produit');
            $mouvementProduit->setProduit($produit);

            /* 
            Todo : Enregistrer la charge (depense) : ajout stock
             et enregistrer une nouvelle transaction dans la caisse.
            */
            $depense->setType('Achat du produit:' . $produit->getNom());
            $depense->setMontant($produit->getPrixAchat() * $quantite);
            $depense->setDescription('Achat du prémier stock du produit: ' . $produit->getNom());
            $depense->setDateDepense($date);

            $caisse = new Caisse();
            $caisse->setMontant($montant);
            $caisse->setType('Décaissement');
            $caisse->setDate($date);
            $caisse->setDescription('Achat du prémier stock du produit: ' . $produit->getNom());

            $em->persist($produit);
            // $em->persist($mouvementProduit);
            $em->persist($depense);
            $em->persist($caisse);
            $em->flush();
            $this->addFlash('success', 'Le produit a été bien enregistré.');
            return $this->redirectToRoute('app_admin_produit_index');
        }


        return $this->render('admin/produit/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_produit_edit', methods: ['GET', 'POST'])]
    public function edit(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        CaisseRepository $caisseRepository,
        MouvementStockRepository $mouvementStockRepository
    ): Response {
        $ancienneQuantite = $produit->getQuantiteStock();

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouvelleQuantite = $produit->getQuantiteStock();
            $date = new DateTime();

            if ($nouvelleQuantite < $ancienneQuantite) {
                $diff = $ancienneQuantite - $nouvelleQuantite;
                $montantAjuste = $produit->getPrixAchat() * $diff;

                // Caisse : remboursement
                $caisse = new Caisse();
                $caisse->setMontant($montantAjuste);
                $caisse->setType('Encaissement');
                $caisse->setDate($date);
                $caisse->setDescription('Correction stock (diminution) du produit: ' . $produit->getNom() . ' (-' . $diff . ' unités)');
                $em->persist($caisse);

                // Dépense : correction négative
                $depense = new Depense();
                $depense->setType('Correction stock');
                $depense->setMontant(-$montantAjuste);
                $depense->setDescription('Correction stock (diminution) du produit: ' . $produit->getNom() . ' (-' . $diff . ' unités)');
                $depense->setDateDepense($date);
                $em->persist($depense);

                // ── Mouvement : on cherche le mouvement "Première entrée" et on met à jour sa quantité ──
                $mouvementInitial = $mouvementStockRepository->findOneBy([
                    'produit'       => $produit,
                    'typeMouvement' => 'Prémière entrée du produit'
                ]);
                if ($mouvementInitial) {
                    $mouvementInitial->setQuantite($nouvelleQuantite);
                }
            } elseif ($nouvelleQuantite > $ancienneQuantite) {
                $diff = $nouvelleQuantite - $ancienneQuantite;
                $montantSupp = $produit->getPrixAchat() * $diff;

                $sommeCaisse = $caisseRepository->getEtatCaisse();
                if ($montantSupp > $sommeCaisse) {
                    $this->addFlash('danger', 'Solde insuffisant.');
                    return $this->redirectToRoute('app_admin_produit_index');
                }

                // Caisse : décaissement supplémentaire
                $caisse = new Caisse();
                $caisse->setMontant($montantSupp);
                $caisse->setType('Décaissement');
                $caisse->setDate($date);
                $caisse->setDescription('Correction stock (augmentation) du produit: ' . $produit->getNom() . ' (+' . $diff . ' unités)');
                $em->persist($caisse);

                // Dépense supplémentaire
                $depense = new Depense();
                $depense->setType('Achat stock');
                $depense->setMontant($montantSupp);
                $depense->setDescription('Correction stock (augmentation) du produit: ' . $produit->getNom() . ' (+' . $diff . ' unités)');
                $depense->setDateDepense($date);
                $em->persist($depense);

                // ── Mouvement : on met à jour la quantité du mouvement initial ──
                $mouvementInitial = $mouvementStockRepository->findOneBy([
                    'produit'       => $produit,
                    'typeMouvement' => 'Prémière entrée du produit'
                ]);
                if ($mouvementInitial) {
                    $mouvementInitial->setQuantite($nouvelleQuantite);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Le produit a été bien modifié.');
            return $this->redirectToRoute('app_admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_produit_delete', methods: ['DELETE'])]
    public function delete(
        Produit $produit,
        MouvementStockRepository $mbr,
        EntityManagerInterface $em,
        LigneVenteRepository $ligneVenteRepository
    ): Response {
        try {
            // Bloquer si le produit a des ventes associées
            $lignes = $ligneVenteRepository->findBy(['produit' => $produit]);
            if (count($lignes) > 0) {
                $this->addFlash('danger', 'Impossible de supprimer ce produit car il est lié à des ventes.');
                return $this->redirectToRoute('app_admin_produit_index');
            }

            $date = new DateTime();
            $quantite = $produit->getQuantiteStock();
            $montant = $produit->getPrixAchat() * $quantite;

            // Rembourser la caisse uniquement si stock restant > 0
            if ($quantite > 0 && $montant > 0) {
                $caisse = new Caisse();
                $caisse->setMontant($montant);
                $caisse->setType('Encaissement');
                $caisse->setDate($date);
                $caisse->setDescription('Suppression du produit: ' . $produit->getNom());
                $em->persist($caisse);

                $depense = new Depense();
                $depense->setType('Correction stock');
                $depense->setMontant(-$montant);
                $depense->setDescription('Suppression du produit: ' . $produit->getNom());
                $depense->setDateDepense($date);
                $em->persist($depense);
            }

            // Supprimer les mouvements puis le produit
            foreach ($mbr->findBy(['produit' => $produit]) as $mvt) {
                $em->remove($mvt);
            }

            $em->remove($produit);
            $em->flush();

            $this->addFlash('success', 'Le produit a bien été supprimé.');
            return $this->redirectToRoute('app_admin_produit_index');
        } catch (\Throwable $th) {
            $this->addFlash('danger', 'Impossible de supprimer ce produit.');
            return $this->redirectToRoute('app_admin_produit_index');
        }
    }

    #[Route('/{id}/ajout-stock', name: 'app_admin_produit_ajout_stock', methods: ['POST'])]
    public function ajoutStock(Produit $produit, Request $request, EntityManagerInterface $em, CaisseRepository $caisseRepository): Response
    {
        /* Je recupere les valeurs du formulaire */
        $quantite = (int)$request->request->get('quantite');
        $date = new DateTime();
        $depense = new Depense();

        $sommeCaisse = $caisseRepository->getEtatCaisse();
        $montant = $produit->getPrixAchat() * $quantite;

        if ($montant > $sommeCaisse) {
            $this->addFlash('danger', 'Solde insuffisant.');
            return $this->redirectToRoute('app_admin_produit_index');
        }

        $mouvementProduit = new MouvementStock();
        $mouvementProduit->setQuantite($quantite);
        $mouvementProduit->setDate($date);
        $mouvementProduit->setTypeMouvement('entrée');
        $mouvementProduit->setProduit($produit);

        $quantiteInitiale = $produit->getQuantiteStock();
        $quantiteTotale = $quantiteInitiale + $quantite;
        $produit->setQuantiteStock($quantiteTotale);

        /* 
            Todo : Enregistrer la charge (depense) : ajout stock
             et enregistrer une nouvelle transaction dans la caisse.
        */
        $depense->setType('achat stock');
        $depense->setMontant($produit->getPrixAchat() * $quantite);
        $depense->setDescription('Augmentation du stock du produit: ' . $produit->getNom());
        $depense->setDateDepense($date);

        $caisse = new Caisse();
        $caisse->setMontant($montant);
        $caisse->setType('Décaissement');
        $caisse->setDate($date);
        $caisse->setDescription('Augmentation du stock du produit: ' . $produit->getNom());

        $em->persist($produit);
        $em->persist($depense);
        $em->persist($caisse);
        $em->persist($mouvementProduit);
        $em->flush();
        $this->addFlash('success', 'Le stock a été bien augmenté.');

        return $this->redirectToRoute('app_admin_produit_index');
    }


    #[Route('/{id}/mouvement-produit', name: 'app_admin_produit_mouvement_produit', methods: ['GET'])]
    public function voirMouvementStock(Request $request, Produit $produit, MouvementStockRepository $mouvementStockRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $mouvementsStock = $mouvementStockRepository->paginateMouvementsStock($produit, $page);
        return $this->render('admin/produit/mouvementStock.html.twig', [
            'mouvementsStock' => $mouvementsStock,
        ]);
    }

    #[Route('/search', name: 'app_admin_produit_search', methods: ['GET'])]
    public function search(Request $request, ProduitRepository $produitRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query) {
            $produits = $produitRepository->paginateProduitsWithSearch($query, $page);
        } else {
            $produits = [];
        }
        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produits,
            'query' => $query
        ]);
    }

    #[Route('/mouvement-produit/search', name: 'app_admin_mouvement_produit_search', methods: ['GET'])]
    public function searchMouvement(Request $request, MouvementStockRepository $mouvementStockRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query or $query == '') {
            $mouvementsStock = $mouvementStockRepository->paginateMouvementBobineWithSearch($query, $page);
        } else {
            $mouvementsStock = [];
        }
        return $this->render('admin/produit/mouvementStock.html.twig', [
            'mouvementsStock' => $mouvementsStock
        ]);
    }

    #[Route('/impression', name: 'app_admin_produit_impression', methods: ['GET'])]
    public function printProduit(ProduitRepository $pr)
    {
        $produits = $pr->findAll();
        return $this->render('admin/produit/printProduit.html.twig', [
            'produits' => $produits
        ]);
    }
}
