<?php

namespace App\Controller;

use App\Entity\ReglementFacture;
use App\Repository\ReglementFactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reglement/facture')]
class ReglementFactureController extends AbstractController
{
    #[Route('/', name: 'app_admin_reglement_facture_index', methods:['GET'])]
    public function index(Request $request, ReglementFactureRepository $reglementFactureRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/reglement_facture/index.html.twig', [
            'reglements' => $reglementFactureRepository->paginateReglementFactures($page),
        ]);
    }

    #[Route('/{id}/impression', name: 'app_admin_reglement_impression', methods:['GET'])]
    public function imprimerFacture(ReglementFacture $reglement): Response
    {
        return $this->render('admin/reglement_facture/printReglement.html.twig',[
            'r' => $reglement
        ]);
    }

    #[Route('/search', name: 'app_admin_reglement_facture_search', methods:['GET'])]
    public function search(Request $request, ReglementFactureRepository $reglementFactureRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $reglements = $reglementFactureRepository->paginateReglementFacturesWithSearch($query, $page);
        }else{
            $reglements = [];
        }
        return $this->render('admin/reglement_facture/index.html.twig', [
            'reglements' => $reglements,
            'query' => $query
        ]);
    }
}
