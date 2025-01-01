<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Form\CaisseType;
use App\Repository\CaisseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/caisse')]
class CaisseController extends AbstractController
{
    #[Route('/', name: 'app_admin_caisse_index', methods:['GET'])]
    public function index(Request $request, CaisseRepository $caisseRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/caisse/index.html.twig', [
            'caisses' => $caisseRepository->paginateCaisses($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_caisse_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $caisse = new Caisse();
        $form = $this->createForm(CaisseType::class, $caisse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($caisse);
            $em->flush();
            $this->addFlash('success', 'La caisse a bien été enregistrée.');
            return $this->redirectToRoute('app_admin_caisse_index');
        }
        return $this->render('admin/caisse/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_caisse_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, Caisse $caisse, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CaisseType::class, $caisse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'La caisse a bien été modifiée.');
            return $this->redirectToRoute('app_admin_caisse_index');
        }
        return $this->render('admin/caisse/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_caisse_delete', methods:['DELETE'])]
    public function delete(Caisse $caisse, EntityManagerInterface $em): Response
    {
        $em->remove($caisse);
        $em->flush();
        $this->addFlash('success', 'La caisse a bien été suppprimée.');
        return $this->redirectToRoute('app_admin_caisse_index');
    }

    #[Route('/search', name: 'app_admin_caisse_search', methods:['GET'])]
    public function search(Request $request, CaisseRepository $caisseRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);

        if ($query or $query == '')
        {
            $caisses = $caisseRepository->paginateCaissesWithSearch($query, $page);
        }else{
            $caisses = [];
        }
        return $this->render('admin/caisse/index.html.twig', [
            'caisses' => $caisses,
            'query' => $query
        ]);
    }

}
