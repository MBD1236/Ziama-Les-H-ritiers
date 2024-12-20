<?php

namespace App\Controller;

use App\Entity\MouvementBobine;
use App\Entity\Production;
use App\Form\ProductionType;
use App\Repository\ProductionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/production')]
class ProductionController extends AbstractController
{
    #[Route('/', name: 'app_admin_production_index')]
    public function index(Request $request, ProductionRepository $productionRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/production/index.html.twig', [
            'productions' => $productionRepository->paginateProductions($page),
        ]);
    }


    #[Route('/new', name: 'app_admin_production_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ProductionRepository $productionRepository): Response
    {
        $production = new Production();
        $form = $this->createForm(ProductionType::class, $production);
        $form = $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            
            /* Recuperer la quantite a utiliser et la quantite disponible en stock*/
            $quantiteUtilisee = $production->getQuantiteUtilisee();
            $bobine = $production->getBobine();
            $quantiteStock = $bobine->getQuantiteStock(); //Quantite disponible en stock
            if ($quantiteUtilisee > $quantiteStock)
            {
                $this->addFlash('danger', 'La quantité à utiliser est supérieure à la quantité disponible en stock.');
                return $this->redirectToRoute('app_admin_production_index');
            }
            $currentYear = (new DateTime())->format('Y');
            $lastProduction = $productionRepository->findOneBy([], ['id' => 'DESC']);
            $lastId = $lastProduction ? $lastProduction->getId() + 1 : 1;
            $codeProduction = 'P-' . $lastId . '/' . $currentYear;
            $production->setCodeProduction($codeProduction);
            
            /* Soustraire la quantite utilisée sur la quantite disponible en stock(dans la table bobine),
              enregistrer la nouvelle quantite dans la base de donnée puis retracer l'historique dans
              MouvementBobine */
            $quantiteTotale = $quantiteStock - $quantiteUtilisee;
            $bobine->setQuantiteStock($quantiteTotale);

            $mouvementBobine = new MouvementBobine();
            $mouvementBobine->setTypeMouvement('sortie');
            $mouvementBobine->setQuantite($quantiteUtilisee);
            $mouvementBobine->setPrixUnitaire($bobine->getPrixUnitaire());
            $mouvementBobine->setBobine($bobine);
            $mouvementBobine->setDate(new DateTime());

            $em->persist($production);
            $em->persist($bobine);
            $em->persist($mouvementBobine);
            $em->flush();
            $this->addFlash('success', 'La production a été bien enregistrée.');
            return $this->redirectToRoute('app_admin_production_index');
        }

        return $this->render('admin/production/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_production_edit', methods:['GET', 'POST'])]
    public function edit(Production $production, Request $request, EntityManagerInterface $em): Response
    {
       
        $form = $this->createForm(ProductionType::class, $production);
        $form = $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {  
            $em->flush();
            $this->addFlash('success', 'La production a été bien modifiée.');
            return $this->redirectToRoute('app_admin_production_index');
        }

        return $this->render('admin/production/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/search', name: 'app_admin_production_search', methods:['GET'])]
    public function search(Request $request, ProductionRepository $productionRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $productions = $productionRepository->paginateProductionsWithSearch($query, $page);
        }else{
            $productions = [];
        }
        return $this->render('admin/production/index.html.twig', [
            'productions' => $productions,
            'query' => $query,
            'filtre' => true
        ]);
    }
}
