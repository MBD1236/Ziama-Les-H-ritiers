<?php

namespace App\Controller;

use App\Entity\Cadeau;
use App\Form\CadeauType;
use App\Repository\CadeauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/cadeau')]
class CadeauController extends AbstractController
{
    #[Route('/', name: 'app_admin_cadeau_index', methods:['GET'])]
    public function index(Request $request, CadeauRepository $cadeauRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/cadeau/index.html.twig', [
            'cadeaux' => $cadeauRepository->paginateCadeau($page),
        ]);
    }

    #[Route('/new', name: 'app_admin_cadeau_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $cadeau = new Cadeau();
        $form = $this->createForm(CadeauType::class, $cadeau);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $production = $cadeau->getProduction();
            $quantite = $cadeau->getQuantite();
            $quantiteInitiale = $production->getNombrePack();

            /* Vérifier si le stock n'est pas insuffisant */
            if ($quantite > $quantiteInitiale)
            {
                $this->addFlash('danger', 'Stock insuffisant');
                return $this->redirectToRoute('app_admin_cadeau_index');
            }
            $quantiteFinale = $quantiteInitiale - $quantite;
            $production->setNombrePack($quantiteFinale);

            $em->persist($production);
            $em->persist($cadeau);
            $em->flush();
            $this->addFlash('success', 'le cadeau a bien été enregistré.');
            return $this->redirectToRoute('app_admin_cadeau_index');
        }
        return $this->render('admin/cadeau/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_cadeau_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, Cadeau $cadeau, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CadeauType::class, $cadeau);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'le cadeau a bien été modifié.');
            return $this->redirectToRoute('app_admin_cadeau_index');
        }
        return $this->render('admin/cadeau/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_cadeau_delete', methods: ['DELETE'])]
    public function delete(Cadeau $cadeau, EntityManagerInterface $em): Response
    {
        /* Je remet la quantité du cadeau dans le stock(Production) */
        $production = $cadeau->getProduction();
        $nbrePack = $production->getNombrePack();
        $production->setNombrePack($nbrePack + $cadeau->getQuantite());

        $em->remove($cadeau);
        $em->flush();
        $this->addFlash('success', 'Le cadeau a bien été supprimé.');
        return $this->redirectToRoute('app_admin_cadeau_index');
    }

    #[Route('/search', name: 'app_admin_cadeau_search', methods:['GET'])]
    public function search(Request $request, CadeauRepository $cadeauRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);

        if ($query or $query == '')
        {
            $cadeaux = $cadeauRepository->paginateCadeauWithSearch($query, $page);
        }else{
            $cadeaux = [];
        }
        return $this->render('admin/cadeau/index.html.twig', [
            'cadeaux' => $cadeaux,
            'query' => $query
        ]);
    }

}
