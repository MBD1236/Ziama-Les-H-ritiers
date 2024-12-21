<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\TransactionFournisseur;
use App\Form\TransactionFournisseurType;
use App\Repository\TransactionFournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction/fournisseur')]
class TransactionFournisseurController extends AbstractController
{
    
     #[Route('/', name: 'app_admin_transaction_fournisseur_index', methods:['GET'])]
     public function index(Request $request, TransactionFournisseurRepository $transactionFournisseurRepository): Response
     {
        $page = $request->query->getInt('page', 1);
         return $this->render('admin/transaction_fournisseur/index.html.twig', [
             'transactions' => $transactionFournisseurRepository->paginateTransactionFournisseurs($page),
         ]);
     }
 
     #[Route('/new', name: 'app_admin_transaction_fournisseur_new', methods:['GET','POST'])]
     public function transactionFournisseurs(Request $request, EntityManagerInterface $em): Response
     {
         $transactionFournisseur = new TransactionFournisseur();
         $form = $this->createForm(TransactionFournisseurType::class, $transactionFournisseur);
         $form->handleRequest($request);
 
         if ($form->isSubmitted() && $form->isValid())
         {
             /* Enregistrer le montantRegle dans la caisse */
             $caisse = new Caisse();
             $caisse->setDate($transactionFournisseur->getDateTransaction());
             $caisse->setType('Décaissement');
             $caisse->setMontant($transactionFournisseur->getMontant());
             $caisse->setDescription('Paiement d\'un fournisseur');
 
             $em->persist($transactionFournisseur);
             $em->persist($caisse);
             $em->flush();
             $this->addFlash('success', 'La  transaction du fournisseur a bien été enregistré.');
             return $this->redirectToRoute('app_admin_transaction_fournisseur_index');
         }
 
         return $this->render('admin/transaction_fournisseur/new.html.twig', [
             'form' => $form
         ]);
 
     }

     #[Route('/search', name: 'app_admin_transaction_fournisseur_search', methods:['GET'])]
     public function search(Request $request, TransactionFournisseurRepository $transactionFournisseurRepository): Response
     {
         $query = $request->query->get('recherche');
         $page = $request->query->getInt('page', 1);
         if ($query)
         {
            $transactions = $transactionFournisseurRepository->paginateTransactionFournisseursWithSearch($query, $page);
         }else{
             $transactions = [];
         }
         return $this->render('admin/transaction_fournisseur/index.html.twig', [
             'transactions' => $transactions,
             'query' => $query
         ]);
     }
}
