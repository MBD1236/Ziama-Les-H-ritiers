<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Facture;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\FactureRepository;
use App\Repository\ProductionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_admin_commande_index', methods:['GET'])]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $commandeRepository->paginateCommandes($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_commande_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CommandeRepository $commandeRepository,
        FactureRepository $factureRepository): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            /* Il faut au préalable vérifier que la quantité de pack a commander existe dans la 
                production a commander */
            $production = $commande->getProduction();
            if ($commande->getQuantite() > $production->getNombrePack())
            {
                $this->addFlash('danger', 'Stock insuffisant pour cette commande.');
                return $this->redirectToRoute('app_admin_commande_index');
            }
            // Générer le codeCommande pour la commande
            $currentYear = (new DateTime())->format('Y');
            $lastCommande = $commandeRepository->findOneBy([], ['id' => 'DESC']);
            $lastId = $lastCommande ? $lastCommande->getId() + 1 : 1;
            $codeCommande = 'C-' . $lastId . '/' . $currentYear;

            // Générer le codeFacture pour la facture
            $lastFacture = $factureRepository->findOneBy([], ['id' => 'DESC']);
            $lastId = $lastFacture ? $lastFacture->getId() + 1 : 1;
            $codeFacture = 'F-' . $lastId . '/' . $currentYear;
            
            $montantTotal = $commande->getQuantite() * 5000;
            $commande->setMontantTotal($montantTotal);
            $commande->setCodeCommande($codeCommande);
            $commande->setStatut('Non réglé');
            
            /* Je vais enregistrer la commande puis generer la facture dans la table facture*/
            $facture = new Facture();
            $facture->setCommande($commande);
            $facture->setCodeFacture($codeFacture);
            $facture->setMontantRegle(0);
            $facture->setMontantRestant($montantTotal);
            $facture->setStatut('Non réglé');

            /* Mettre à jour la quantite de pack dans la production commandée */
            $quantiteInitiale = $production->getNombrePack();
            $production->setNombrePack($quantiteInitiale - $commande->getQuantite());
            
            $em->persist($commande);
            $em->persist($production);
            $em->persist($facture);
            $em->flush();

            $this->addFlash('success', 'La commande est enregistrée et la facture a été bien générée.');
            return $this->redirectToRoute('app_admin_commande_index');
        }
        return $this->render('admin/commande/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_commande_edit', methods:['GET', 'POST'])]
    public function edit(Commande $commande, FactureRepository $factureRepository, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $montantTotal = $commande->getQuantite() * 5000;
            $commande->setMontantTotal($montantTotal);
            $facture = $factureRepository->findOneBy(['commande' => $commande]);
            $facture->setMontantRestant($montantTotal);

            $em->flush();
            $this->addFlash('success', 'La commande a été bien modifiée.');
            return $this->redirectToRoute('app_admin_commande_index');
        }
        return $this->render('admin/commande/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commande_delete', methods: ['DELETE'])]
    public function delete(Commande $commande, FactureRepository $factureRepository, ProductionRepository $productionRepository, EntityManagerInterface $em): Response
    {
        /* Je supprime la facture correspondante puis remet la quantité commandée dans le stock(Production) */
        $facture = $factureRepository->findOneBy(['commande' => $commande]);
        $production = $commande->getProduction();
        $nbrePack = $production->getNombrePack();
        $production->setNombrePack($nbrePack + $commande->getQuantite());

        $em->remove($facture);
        $em->remove($commande);
        $em->flush();
        $this->addFlash('success', 'Le commande a bien été supprimée.');
        return $this->redirectToRoute('app_admin_commande_index');
    }

    #[Route('/{id}/facture', name: 'app_admin_commande_facture', methods:['GET'])]
    public function voirFacture(Commande $commande, FactureRepository $factureRepository): Response
    {
        $facture = $factureRepository->findOneBy(['commande' => $commande]);
        return $this->render('admin/commande/facture.html.twig', [
            'facture' => $facture
        ]);
    }

    #[Route('/search', name: 'app_admin_commande_search', methods:['GET'])]
    public function search(Request $request, CommandeRepository $commandeRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query or $query == '')
        {
            $commandes = $commandeRepository->paginateCommandesWithSearch($query, $page);
        }else{
            $commandes = [];
        }
        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $commandes,
            'query' => $query
        ]);
    }
}
