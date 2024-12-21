<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Form\LivraisonType;
use App\Repository\CommandeRepository;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/livraison')]
class LivraisonController extends AbstractController
{
    #[Route('/', name: 'app_admin_livraison_index', methods:['GET'])]
    public function index(Request $request, LivraisonRepository $livraisonRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/livraison/index.html.twig', [
            'livraisons' => $livraisonRepository->paginateLivraisons($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_livraison_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, LivraisonRepository $livraisonRepository): Response
    {
        $livraison  = new Livraison();
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
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
    public function search(Request $request, LivraisonRepository $livraisonRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $livraisons = $livraisonRepository->paginateLivraisonsWithSearch($query, $page);
        }else{
            $livraisons = [];
        }
        return $this->render('admin/livraison/index.html.twig', [
            'livraisons' => $livraisons,
            'query' => $query
        ]);
    }
}
