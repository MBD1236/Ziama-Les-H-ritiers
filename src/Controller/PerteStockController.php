<?php

namespace App\Controller;

use App\Entity\PerteStock;
use App\Entity\MouvementStock;
use App\Form\PerteStockType;
use App\Repository\MouvementStockRepository;
use App\Repository\PerteStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/perte-stock')]
class PerteStockController extends AbstractController
{
    #[Route('/', name: 'app_admin_perte_stock_index', methods: ['GET'])]
    public function index(PerteStockRepository $perteStockRepository): Response
    {
        return $this->render('admin/perte_stock/index.html.twig', [
            'pertes' => $perteStockRepository->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_admin_perte_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $perte = new PerteStock();
        $form  = $this->createForm(PerteStockType::class, $perte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit  = $perte->getProduit();
            $quantite = $perte->getQuantite();

            // Vérifier que le stock est suffisant
            if ($quantite > $produit->getQuantiteStock()) {
                $this->addFlash('danger', 'La quantité perdue dépasse le stock disponible (' . $produit->getQuantiteStock() . ').');
                return $this->redirectToRoute('app_admin_perte_stock_new');
            }

            // Débiter le stock
            $produit->setQuantiteStock($produit->getQuantiteStock() - $quantite);
            // Enregistrer la perte
            $perte->setDate(new \DateTimeImmutable());
            $perte->setUser($this->getUser());
            $em->persist($perte);
            $em->flush();
            

            // MouvementStock
            $mouvement = new MouvementStock();
            $mouvement->setProduit($produit);
            $mouvement->setQuantite($quantite);
            $mouvement->setTypeMouvement('Perte#' . $perte->getId() . ' - ' . $perte->getMotif());
            $mouvement->setDate(new \DateTime());
            $em->persist($mouvement);


            $em->flush();

            $this->addFlash('success', 'Perte enregistrée. Stock mis à jour.');
            return $this->redirectToRoute('app_admin_perte_stock_index');
        }

        return $this->render('admin/perte_stock/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/delete', name: 'app_admin_perte_stock_delete', methods: ['POST'])]
    public function delete(
        PerteStock $perte,
        EntityManagerInterface $em,
        MouvementStockRepository $mouvementStockRepository
    ): Response {


        try {
            // Supprimer le MouvementStock lié à cette perte
            $mouvement = $mouvementStockRepository->findOneBy([
                'typeMouvement' => 'Perte#' . $perte->getId() . ' - ' . $perte->getMotif(),
            ]);

            if ($mouvement) {
                $em->remove($mouvement);
            }

            // Recréditer le stock
            $perte->getProduit()->setQuantiteStock(
                $perte->getProduit()->getQuantiteStock() + $perte->getQuantite()
            );

            $em->remove($perte);
            $em->flush();

            $this->addFlash('success', 'Perte supprimée. Stock restauré.');
            return $this->redirectToRoute('app_admin_perte_stock_index');
        } catch (\Throwable $th) {
            $this->addFlash('danger', 'Erreur de suppression.');
            return $this->redirectToRoute('app_admin_perte_stock_index');
        }
    }
}
