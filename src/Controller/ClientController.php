<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_admin_client_index', methods:['GET'])]
    public function index(Request $request, ClientRepository $clientRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('admin/client/index.html.twig', [
            'clients' => $clientRepository->paginateClients($page)
        ]);
    }

    #[Route('/new', name: 'app_admin_client_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($client);
            $em->flush();
            $this->addFlash('success', 'Le client a été bien enregistrée.');
            return $this->redirectToRoute('app_admin_client_index');
        }
        return $this->render('admin/client/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_edit', methods:['GET', 'POST'])]
    public function edit(Client $client,Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'Le client a été bien modifié.');
            return $this->redirectToRoute('app_admin_client_index');
        }
        return $this->render('admin/client/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_delete', methods: ['DELETE'])]
    public function delete(Client $client, EntityManagerInterface $em): Response
    {
        $em->remove($client);
        $em->flush();
        $this->addFlash('success', 'Le client a bien été supprimé.');
        return $this->redirectToRoute('app_admin_client_index');
    }

    #[Route('/search', name: 'app_admin_client_search', methods:['GET'])]
    public function search(Request $request, ClientRepository $clientRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query)
        {
            $clients = $clientRepository->paginateWithSearch($query, $page);
        }else{
            $clients = [];
        }
        return $this->render('admin/client/index.html.twig', [
            'clients' => $clients,
            'query' => $query
        ]);
    }
}
