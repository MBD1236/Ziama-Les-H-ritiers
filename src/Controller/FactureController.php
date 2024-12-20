<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\Facture;
use App\Entity\ReglementFacture;
use App\Form\ReglementFactureType;
use App\Repository\FactureRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/facture')]
class FactureController extends AbstractController
{
    #[Route('/', name: 'app_admin_facture_index', methods:['GET'])]
    public function index(Request $request, FactureRepository $factureRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/facture/index.html.twig', [
            'factures' => $factureRepository->paginateFactures($page),
        ]);
    }

    #[Route('/{id}/regler-facture', name: 'app_admin_facture_reglement_facture', methods:['POST'])]
    public function reglementFacture(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        $modeReglement = $request->request->get('modeReglement');
        $montantRegle = (int)$request->request->get('montantRegle');
        $dateReglement = new DateTime();

        /* Je fais la mise a jour du montantRestant dans la table facture*/
        $montantActuel = $facture->getMontantRestant();
        $montantRestant = $montantActuel - $montantRegle;
        if ($montantRestant == 0) {
            $facture->setStatut('Réglé');
            $facture->getCommande()->setStatut('Réglé');
        }
        $facture->setMontantRegle($montantRegle);
        $facture->setMontantRestant($montantRestant);
        
        $reglementFacture = new ReglementFacture();
        $reglementFacture->setFacture($facture);
        $reglementFacture->setModeReglement($modeReglement);
        $reglementFacture->setMontantRegle($montantRegle);
        $reglementFacture->setDate($dateReglement);

        /* Enregistrer le montantRegle dans la caisse */
        $caisse = new Caisse();
        $caisse->setDate($dateReglement);
        $caisse->setType('Encaissement');
        $caisse->setMontant($montantRegle);
        $caisse->setDescription('Paiement d\'un client');

        $em->persist($facture);
        $em->persist($reglementFacture);
        $em->persist($caisse);
        $em->flush();

        $this->addFlash('success', 'La facture a été bien réglé.');
        return $this->redirectToRoute('app_admin_reglement_facture_index');
    }

    #[Route('/{id}/impression', name: 'app_admin_facture_impression', methods:['GET'])]
    public function imprimerFacture(Facture $facture): Response
    {
        return $this->render('admin/facture/printFacture.html.twig',[
            'facture' => $facture
        ]);
    }

    #[Route('/search', name: 'app_admin_facture_search', methods:['GET'])]
    public function search(Request $request, FactureRepository $factureRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $factures = $factureRepository->paginateFacturesWithSearch($query, $page);
        }else{
            $factures = [];
        }
        return $this->render('admin/facture/index.html.twig', [
            'factures' => $factures,
            'query' => $query
        ]);
    }
}
