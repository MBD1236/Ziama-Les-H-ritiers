<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\Commande;
use App\Entity\Facture;
use App\Entity\Livraison;
use App\Entity\ReglementFacture;
use App\Form\LivraisonType;
use App\Repository\CommandeRepository;
use App\Repository\FactureRepository;
use App\Repository\LivraisonRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/livraison')]
class LivraisonController extends AbstractController
{
    #[Route('/', name: 'app_admin_livraison_index', methods:['GET'])]
    public function index(Request $request, LivraisonRepository $livraisonRepository, Security $security): Response
    {
        $user = $security->getUser();
        $page = $request->query->getInt('page', 1);
        if ($user->getRoles() == ['ROLE_ADMIN']) {
            return $this->render('admin/livraison/index.html.twig', [
                'livraisons' => $livraisonRepository->paginateLivraisons($page),
            ]);
        }else{
            return $this->render('admin/livraison/index.html.twig', [
                'livraisons' => $livraisonRepository->paginateLivraisons2($user, $page),
            ]);
        }
    }

    #[Route('/new', name: 'app_admin_livraison_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, LivraisonRepository $livraisonRepository, Security $security): Response
    {
        $livraison  = new Livraison();
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        $user = $security->getUser();
        if ($form->isSubmitted() && $form->isValid())
        {
            if ($user->getRoles() == ['ROLE_ADMIN']) {
                $commande = $livraison->getCommande();
                $verification = $livraisonRepository->findOneBy(['commande' => $commande]);
                if ($verification) {
                    $this->addFlash('danger', 'Cette commande est deja livrée par un autre livreur.');
                    return $this->redirectToRoute('app_admin_livraison_index');
                }
                if ($livraison->getUser()->getRoles() !== ['ROLE_LIVREUR']){
                    $this->addFlash('danger', 'Cet employé n\'est pas un livreur.');
                    return $this->redirectToRoute('app_admin_livraison_index');
                }
                $em->persist($livraison);
                $em->flush();
                $this->addFlash('success', 'La livraison a bien été enregistrée.');
                return $this->redirectToRoute('app_admin_livraison_index');
            }else{
                $employe = $livraison->getUser();
                if ($user !== $employe){
                    $this->addFlash('danger', 'Veuillez choisir vos informations.');
                    return $this->redirectToRoute('app_admin_livraison_index');
                }
                $commande = $livraison->getCommande();
                $verification = $livraisonRepository->findOneBy(['commande' => $commande]);
                if ($verification) {
                    $this->addFlash('danger', 'Cette commande est deja livrée par un autre livreur.');
                    return $this->redirectToRoute('app_admin_livraison_index');
                }
                $em->persist($livraison);
                $em->flush();
                $this->addFlash('success', 'La livraison a bien été enregistrée.');
                return $this->redirectToRoute('app_admin_livraison_index');
            }
        }
        return $this->render('admin/livraison/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/new', name: 'app_admin_livraison_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, Livraison $livraison, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'La livraison a bien été modifiée.');
            return $this->redirectToRoute('app_admin_livraison_index');
        }
        return $this->render('admin/livraison/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_livraison_delete', methods:['DELETE'])]
    public function delete(Request $request, Livraison $livraison, EntityManagerInterface $em): Response
    {
        $em->remove($livraison);
        $em->flush();
        $this->addFlash('success', 'La livraison a bien été supprimée.');
        return $this->redirectToRoute('app_admin_livraison_index');
    }

    #[Route('/search', name: 'app_admin_livraison_search', methods:['GET'])]
    public function search(Request $request, LivraisonRepository $livraisonRepository, Security $security): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        $user = $security->getUser();
        if ($user->getRoles() == ['ROLE_ADMIN']) {
            if ($query or $query == '')
            {
                    $livraisons = $livraisonRepository->paginateLivraisonsWithSearch($query, $page);
            }else{
                    $livraisons = [];
            }
                return $this->render('admin/livraison/index.html.twig', [
                    'livraisons' => $livraisons,
                    'query' => $query
                ]);
        }else{
            if ($query or $query == '')
            {
                $livraisons = $livraisonRepository->paginateLivraisonsWithSearch2($user, $query, $page);
            }else{
                $livraisons = [];
            }
                return $this->render('admin/livraison/index.html.twig', [
                    'livraisons' => $livraisons,
                    'query' => $query
                ]);
        }
        
    }

    #[Route('/{id}/facture', name: 'app_admin_livraison_facture', methods:['GET'])]
    public function voirFacture(Commande $commande, FactureRepository $factureRepository): Response
    {
        $facture = $factureRepository->findOneBy(['commande' => $commande]);
        return $this->render('admin/livraison/facture.html.twig', [
            'facture' => $facture
        ]);
    }

    #[Route('/{id}/regler-facture', name: 'app_admin_livraison_reglement_facture', methods:['POST'])]
    public function reglementFacture(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        $modeReglement = $request->request->get('modeReglement');
        $montantRegle = (int)$request->request->get('montantRegle');
        $dateReglement = new DateTime();

        /* Je fais la mise a jour du montantRestant dans la table facture*/
        $montantActuel = $facture->getMontantRestant();
        if ($montantRegle > $montantActuel) {
            $this->addFlash('danger', 'Erreur sur le montant à payer.');
            return $this->redirectToRoute('app_admin_livraison_index');
        }
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

        $this->addFlash('success', 'La facture a été bien réglée.');
        return $this->redirectToRoute('app_admin_livraison_index');
    }
    #[Route('/{id}/impression', name: 'app_admin_livraison_facture_impression', methods:['GET'])]
    public function imprimerFacture(Facture $facture): Response
    {
        return $this->render('admin/livraison/printFacture.html.twig',[
            'facture' => $facture
        ]);
    }
}
