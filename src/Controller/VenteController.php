<?php

namespace App\Controller;

use App\Entity\Vente;
use App\Entity\Facture;
use App\Entity\LigneVente;
use App\Form\LigneVenteType;
use App\Form\VenteType;
use App\Repository\VenteRepository;
use App\Repository\FactureRepository;
use App\Repository\MouvementStockRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/vente')]
class VenteController extends AbstractController
{
    #[Route('/', name: 'app_admin_vente_index', methods: ['GET'])]
    public function index(Request $request, VenteRepository $venteRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/vente/index.html.twig', [
            'ventes' => $venteRepository->paginateVentes($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, VenteRepository $venteRepository): Response
    {
        $vente = new Vente();
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Générer le code vente
            $currentYear = (new DateTime())->format('Y');
            $lastVente = $venteRepository->findOneBy([], ['id' => 'DESC']);
            $lastId = $lastVente ? $lastVente->getId() + 1 : 1;
            $codeVente = 'V-' . $lastId . '/' . $currentYear;

            $vente->setCodeVente($codeVente);
            $vente->setStatut('Non réglé');
            $vente->setDateVente(new DateTime());

            // Enregistrer la vente SANS facture pour le moment
            $em->persist($vente);
            $em->flush();

            $this->addFlash('success', 'La vente a été créée. Ajoutez les lignes de produits ci-dessous.');
            return $this->redirectToRoute('app_admin_vente_add_lignes', ['id' => $vente->getId()]);
        }

        return $this->render('admin/vente/new.html.twig', [
            'form' => $form
        ]);
    }



    #[Route('/{id}/lignes/add', name: 'app_admin_vente_add_lignes', methods: ['GET', 'POST'])]
    public function addLignes(Vente $vente, FactureRepository $factureRepository, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier qu'il y a au moins une ligne
            if (empty($vente->getLignes())) {
                $this->addFlash('warning', 'Veuillez ajouter au moins une ligne de produit.');
                return $this->render('admin/vente/add_lignes.html.twig', [
                    'form' => $form,
                    'vente' => $vente
                ]);
            }

            // Calculer le montant total depuis les lignes de vente
            $montantTotal = 0;
            $stocksModifies = 0;
            $detailsStock = [];

            foreach ($vente->getLignes() as $ligne) {
                $produit = $ligne->getProduit();
                $quantiteVendue = $ligne->getQuantite() ?? 0;
                $prixUnitaire = $ligne->getPrixUnitaire() ?? 0;

                // ✅ 1. Calculer le totalLigne
                $totalLigne = $quantiteVendue * $prixUnitaire;
                $ligne->setTotalLigne($totalLigne);
                $montantTotal += $totalLigne;


                // ✅ 2. Vérifier le stock
                if ($produit && $quantiteVendue > 0) {
                    $stockActuel = $produit->getQuantiteStock();


                    // Le LigneVenteListener gère automatiquement la soustraction du stock
                    // et la création du MouvementStock de type "sortie"
                    // Ici on vérifie juste que le stock n'est pas négatif après la soustraction
                    if (($stockActuel - $quantiteVendue) < 0) {
                        $this->addFlash('danger', 'Stock insuffisant pour ' . $produit->getNom() .
                            ' (disponible: ' . $stockActuel . ', demandé: ' . $quantiteVendue . ')');
                        return $this->render('admin/vente/add_lignes.html.twig', [
                            'form' => $form,
                            'vente' => $vente
                        ]);
                    }

                    // ✅ 3. Soustraire la quantité du produit
                    $produit->setQuantiteStock($stockActuel - $quantiteVendue);
                    $detailsStock[] = $produit->getNom() . ' (-' . $quantiteVendue . ')';


                    // ✅ 4. Créer le MouvementStock de type "sortie"
                    $mouvement = new \App\Entity\MouvementStock();
                    $mouvement->setProduit($produit);
                    $mouvement->setTypeMouvement('sortie');
                    $mouvement->setQuantite($quantiteVendue);
                    $mouvement->setDate(new DateTime());

                    $em->persist($mouvement);
                }
            }

            // ✅ 5. Gérer la facture
            $facture = $factureRepository->findOneBy(['vente' => $vente]);

            if (!$facture) {
                // Créer la facture si elle n'existe pas (première sauvegarde avec lignes)
                $currentYear = (new DateTime())->format('Y');
                $lastFacture = $factureRepository->findOneBy([], ['id' => 'DESC']);
                $lastId = $lastFacture ? $lastFacture->getId() + 1 : 1;
                $codeFacture = 'F-' . $lastId . '/' . $currentYear;

                $facture = new Facture();
                $facture->setVente($vente);
                $facture->setCodeFacture($codeFacture);
                $facture->setMontantRegle(0);
                $facture->setMontantRestant($montantTotal);
                $facture->setStatut('Non réglé');

                $em->persist($facture);
                $message = 'Vente enregistrée avec succès ! ' . $stocksModifies . ' produit(s) ont été soustrait du stock et la facture a été créée.';
            } else {
                // Mettre à jour le montant restant de la facture existante
                $facture->setMontantRestant($montantTotal);
                $message = 'Vente mise à jour ! ' . $stocksModifies . ' produit(s) mis en mouvement et la facture a été recalculée.';
            }

            // Persister tous les mouvements de stock (gérés par le LigneVenteListener)
            $em->flush();

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_admin_vente_index');
        }

        return $this->render('admin/vente/add_lignes.html.twig', [
            'form' => $form,
            'vente' => $vente
        ]);
    }

    #[Route('/{id}', name: 'app_admin_vente_delete', methods: ['DELETE'])]
    public function delete(Vente $vente, FactureRepository $factureRepository, EntityManagerInterface $em, MouvementStockRepository $msr): Response
    {
        // 1. Remettre les quantités en stock et supprimer les mouvements associés
        foreach ($vente->getLignes() as $ligne) {
            $produit = $ligne->getProduit();
            if ($produit) {
                // Remettre la quantité vendue dans le stock du produit
                $quantiteVendue = $ligne->getQuantite() ?? 0;
                $produit->setQuantiteStock($produit->getQuantiteStock() + $quantiteVendue);
                // Supprimer les mouvements de stock directement associés à cette ligne
                $mouvements = $msr->mouvementProduits($produit);
                foreach ($mouvements as $mouvement) {
                    $em->remove($mouvement);
                }
            }
        }

        // 2. Supprimer la facture correspondante
        $facture = $factureRepository->findOneBy(['vente' => $vente]);
        if ($facture) {
            $em->remove($facture);
        }

        // 3. Supprimer la vente (les lignes seront supprimées en cascade)
        $em->remove($vente);
        $em->flush();

        $this->addFlash('success', 'La vente a bien été supprimée, les stocks ont été remis à jour et la facture a été supprimée.');
        return $this->redirectToRoute('app_admin_vente_index');
    }

    #[Route('/{id}/facture', name: 'app_admin_vente_facture', methods: ['GET'])]
    public function voirFacture(Vente $vente, FactureRepository $factureRepository): Response
    {
        $facture = $factureRepository->findOneBy(['vente' => $vente]);
        return $this->render('admin/vente/facture.html.twig', [
            'facture' => $facture
        ]);
    }

    #[Route('/search', name: 'app_admin_vente_search', methods: ['GET'])]
    public function search(Request $request, VenteRepository $venteRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query or $query == '') {
            $ventes = $venteRepository->paginateVentesWithSearch($query, $page);
        } else {
            $ventes = [];
        }
        return $this->render('admin/vente/index.html.twig', [
            'ventes' => $ventes,
            'query' => $query
        ]);
    }
}
