<?php

namespace App\Controller;

use App\Entity\ProductionEmploye;
use App\Form\ProductionEmployeeType;
use App\Repository\ProductionEmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/production/employe')]
class ProductionEmployeController extends AbstractController
{
    #[Route('/', name: 'app_admin_production_employe_index', methods:['GET'])]
    public function index(Request $request, ProductionEmployeRepository $productionEmployeRepository, Security $security): Response
    {
        $page = $request->query->getInt('page', 1);
        $user = $security->getUser();
        if ($user->getRoles() == ['ROLE_ADMIN']){
            return $this->render('admin/production_employe/index.html.twig', [
                'productionEmployes' => $productionEmployeRepository->paginateProductionEmployes($page),
            ]);
        }else{
            return $this->render('admin/production_employe/index.html.twig', [
                'productionEmployes' => $productionEmployeRepository->paginateProductionEmployes2($user, $page),
            ]);
        }
    }

    #[Route('/new', name: 'app_admin_production_employe_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $productionEmploye = new ProductionEmploye();
        $form = $this->createForm(ProductionEmployeeType::class, $productionEmploye);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $security->getUser();
            if ($user->getRoles() == ['ROLE_ADMIN']){
                if ($productionEmploye->getUser()->getRoles() !== ['ROLE_PRODUCTEUR']){
                    $this->addFlash('danger', 'Cet employé n\'est pas un producteur.');
                    return $this->redirectToRoute('app_admin_production_employe_index');
                }
                $em->persist($productionEmploye);
                $em->flush();
                $this->addFlash('success', 'La production des employes a été bien enregistrée.');
                return $this->redirectToRoute('app_admin_production_employe_index');
            }else{
                $employe = $productionEmploye->getUser();
                if ($user !== $employe){
                    $this->addFlash('danger', 'Veuillez choisir vos informations.');
                    return $this->redirectToRoute('app_admin_production_employe_index');
                }else {
                    $em->persist($productionEmploye);
                    $em->flush();
                    $this->addFlash('success', 'La production a été bien enregistrée.');
                    return $this->redirectToRoute('app_admin_production_employe_index');
                }
            }
        }
        return $this->render('admin/production_employe/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_production_employe_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, ProductionEmploye $productionEmploye, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductionEmployeeType::class, $productionEmploye);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'La production des employes a été bien modifiée.');
            return $this->redirectToRoute('app_admin_production_employe_index');
        }
        return $this->render('admin/production_employe/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_production_employe_delete', methods:['DELETE'])]
    public function delete(ProductionEmploye $productionEmploye, EntityManagerInterface $em): Response
    {
        $em->remove($productionEmploye);
        $em->flush();
        $this->addFlash('success', 'La production des employes a été bien supprimée.');
        return $this->redirectToRoute('app_admin_production_employe_index');
    }

    #[Route('/search', name: 'app_admin_production_employe_search', methods:['GET'])]
    public function search(Request $request, ProductionEmployeRepository $productionEmployeRepository, Security $security): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        $user = $security->getUser();

        if ($user->getRoles() == ['ROLE_ADMIN']){
            if ($query or $query == '')
            {
                $productionEmployes = $productionEmployeRepository->paginateProductionEmployesWithSearch($query, $page);
            }else{
                $productionEmployes = [];
            }
            return $this->render('admin/production_employe/index.html.twig', [
                'productionEmployes' => $productionEmployes,
                'query' => $query
            ]);
        }else{
            if ($query)
            {
                $productionEmployes = $productionEmployeRepository->paginateProductionEmployesWithSearch2($user, $query, $page);
            }else{
                $productionEmployes = [];
            }
            return $this->render('admin/production_employe/index.html.twig', [
                'productionEmployes' => $productionEmployes,
                'query' => $query
            ]);
        }
    }

}
