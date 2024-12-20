<?php

namespace App\Controller;

use App\Entity\Bobine;
use App\Entity\MouvementBobine;
use App\Form\BobineType;
use App\Form\MouvementBobineType;
use App\Repository\BobineRepository;
use App\Repository\MouvementBobineRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/bobine')]
class BobineController extends AbstractController
{
    #[Route('/', name: 'app_admin_bobine_index', methods:['GET'])]
    public function index(Request $request, BobineRepository $bobineRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('admin/bobine/index.html.twig', [
            'bobines' => $bobineRepository->paginateBobines($page)
        ]);
    }

    #[Route('/new', name: 'app_admin_bobine_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $bobine = new Bobine();
        $form = $this->createForm(BobineType::class, $bobine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($bobine);
            $em->flush();
            $this->addFlash('success', 'La bobine a été bien enregistrée.');
            return $this->redirectToRoute('app_admin_bobine_index');
        }

        return $this->render('admin/bobine/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_bobine_edit', methods:['GET', 'POST'])]
    public function edit(Bobine $bobine, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(BobineType::class, $bobine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'La bobine a été bien modifiée.');
            return $this->redirectToRoute('app_admin_bobine_index');
        }

        return $this->render('admin/bobine/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/ajout-stock', name: 'app_admin_bobine_ajout_stock', methods:['POST'])]
    public function ajoutStock(Bobine $bobine, Request $request, EntityManagerInterface $em): Response
    {
        /* Je recupere les valeurs du formulaire */
        $quantite = (int)$request->request->get('quantite');
        $prixUnitaire = (int)$request->request->get('prixUnitaire');
        $date = new DateTime();
        

        $mouvementBobine = new MouvementBobine();
        $mouvementBobine->setQuantite($quantite);
        $mouvementBobine->setPrixUnitaire($prixUnitaire);
        $mouvementBobine->setDate($date);
        $mouvementBobine->setTypeMouvement('entrée');
        $mouvementBobine->setBobine($bobine);
        
        $quantiteInitiale = $bobine->getQuantiteStock();
        $quantiteTotale = $quantiteInitiale + $quantite;
        $bobine->setQuantiteStock($quantiteTotale);
        
        $em->persist($bobine);
        $em->persist($mouvementBobine);
        $em->flush();
        $this->addFlash('success', 'Le stock a été bien augmenté.');

        return $this->redirectToRoute('app_admin_bobine_index');
    }


    #[Route('/{id}/mouvement-bobine', name: 'app_admin_bobine_mouvement_bobine', methods:['GET'])]
    public function voirMouvementStock(Request $request, Bobine $bobine, MouvementBobineRepository $mouvementBobineRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $mouvementsBobine = $mouvementBobineRepository->paginateMouvementsBobine($bobine, $page);
        return $this->render('admin/bobine/mouvementBobine.html.twig', [
            'mouvementsBobine' => $mouvementsBobine
        ]);
    }

    #[Route('/search', name: 'app_admin_bobine_search', methods:['GET'])]
    public function search(Request $request, BobineRepository $bobineRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $bobines = $bobineRepository->paginateBobinesWithSearch($query, $page);
        }else{
            $bobines = [];
        }
        return $this->render('admin/bobine/index.html.twig', [
            'bobines' => $bobines,
            'query' => $query
        ]);
    }
}
