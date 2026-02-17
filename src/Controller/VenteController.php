<?php

namespace App\Controller;

use App\Entity\Vente;
use App\Entity\Facture;
use App\Entity\LigneVente;
use App\Entity\MouvementStock;
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
    public function addLignes(
        Vente $vente,
        FactureRepository $factureRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Créer la ligne de vente
        $ligne = new LigneVente();
        $form = $this->createForm(LigneVenteType::class, $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ligne->setVente($vente);
            $ligne->setProduit($ligne->getProduit());
            $ligne->setQuantite($ligne->getQuantite());
            $ligne->setPrixUnitaire($ligne->getPrixUnitaire());
            $ligne->setTotalLigne($ligne->getQuantite() * $ligne->getPrixUnitaire());

            // Vérifier le stock
            $produit = $ligne->getProduit();
            $stockActuel = $produit->getQuantiteStock();

            if ($stockActuel < $ligne->getQuantite()) {
                $this->addFlash(
                    'danger',
                    'Stock insuffisant pour ' . $produit->getNom() .
                        ' (disponible: ' . $stockActuel . ')'
                );
                return $this->redirectToRoute('app_admin_vente_add_lignes', ['id' => $vente->getId()]);
            }

            // Mettre à jour le stock
            $produit->setQuantiteStock($stockActuel - $ligne->getQuantite());

            // Créer le mouvement de stock
            $mouvement = new MouvementStock();
            $mouvement->setProduit($produit);
            $mouvement->setTypeMouvement('sortie');
            $mouvement->setQuantite($ligne->getQuantite());
            $mouvement->setDate(new DateTime());

            $em->persist($ligne);
            $em->persist($mouvement);
            $em->flush();

            $this->addFlash('success', 'Ligne ajoutée avec succès !');
            return $this->redirectToRoute('app_admin_vente_add_lignes', ['id' => $vente->getId()]);
        }

        // Calculer le total actuel
        $montantTotal = 0;
        foreach ($vente->getLignes() as $ligne) {
            $montantTotal += $ligne->getTotalLigne();
        }

        return $this->render('admin/vente/add_lignes.html.twig', [
            'form' => $form,
            'vente' => $vente,
            'montantTotal' => $montantTotal
        ]);
    }

    #[Route('/{id}/lignes/{ligneId}/delete', name: 'app_admin_vente_delete_ligne', methods: ['POST'])]
    public function deleteLigne(
        Vente $vente,
        int $ligneId,
        EntityManagerInterface $em
    ): Response {
        $ligne = $em->getRepository(LigneVente::class)->find($ligneId);

        if ($ligne && $ligne->getVente()->getId() === $vente->getId()) {
            // Remettre le stock
            $produit = $ligne->getProduit();
            $produit->setQuantiteStock($produit->getQuantiteStock() + $ligne->getQuantite());

            $em->remove($ligne);
            $em->flush();

            $this->addFlash('success', 'Ligne supprimée');
        }

        return $this->redirectToRoute('app_admin_vente_add_lignes', ['id' => $vente->getId()]);
    }

    #[Route('/{id}/finaliser', name: 'app_admin_vente_finaliser', methods: ['POST'])]
    public function finaliser(
        Vente $vente,
        FactureRepository $factureRepository,
        EntityManagerInterface $em
    ): Response {
        if (count($vente->getLignes()) === 0) {
            $this->addFlash('warning', 'Ajoutez au moins une ligne avant de finaliser.');
            return $this->redirectToRoute('app_admin_vente_add_lignes', ['id' => $vente->getId()]);
        }

        // Calculer le montant total
        $montantTotal = 0;
        foreach ($vente->getLignes() as $ligne) {
            $montantTotal += $ligne->getTotalLigne();
        }

        // Créer ou mettre à jour la facture
        $facture = $factureRepository->findOneBy(['vente' => $vente]);

        if (!$facture) {
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
        } else {
            $facture->setMontantRestant($montantTotal);
        }

        $em->flush();

        $this->addFlash('success', 'Vente finalisée et facture créée !');
        return $this->redirectToRoute('app_admin_vente_index');
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
                $mouvements = $msr->findMouvementsByVente($vente);
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

    #[Route('/{id}/details', name: 'app_admin_vente_show', methods: ['GET'])]
    public function show(Vente $vente): Response
    {
        // Calculer le montant total
        $montantTotal = 0;
        foreach ($vente->getLignes() as $ligne) {
            $montantTotal += $ligne->getTotalLigne() ?? ($ligne->getPrixUnitaire() * $ligne->getQuantite());
        }

        return $this->render('admin/vente/show.html.twig', [
            'vente' => $vente,
            'montantTotal' => $montantTotal
        ]);
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
