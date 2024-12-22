<?php

namespace App\Controller;

use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\DepenseRepository;
use App\Repository\LivraisonRepository;
use App\Repository\ProductionRepository;
use App\Repository\TransactionFournisseurRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/accueil')]
class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_admin_accueil_index', methods:['GET'])]
    public function index(ClientRepository $clientRepository, CommandeRepository $commandeRepository,
    CaisseRepository $caisseRepository, UserRepository $userRepository, ChartBuilderInterface $chartBuilder,
    LivraisonRepository $livraisonRepository, ProductionRepository $productionRepository,
    DepenseRepository $depenseRepository, TransactionFournisseurRepository $tfr): Response
    {
        /** Nombre de clients */
        $clients = $clientRepository->countAll();
        /** Etat de la caisse */
        $caisses = $caisseRepository->getEtatCaisse();
        /** Etat des commandes */
        $commandes = $commandeRepository->countAll();
        /** Nombre d'employés */
        $employes = $userRepository->countAll();

        /**** */
        $currentYear = new DateTime();
        $year = $currentYear->format('Y');
        $month = (int)$currentYear->format('m');
        $months = range($month - 4, $month);
        
        $dataCommandes = [];
        $dataLivraisons = [];
        $dataProductions = [];
        $data2Commandes = [];
        $dataDepenses = [];
        $dataTransactions = [];
        foreach ($months as $month) {
            $dataCommandes[] = $commandeRepository->countOrdersByMonth($year, $month);
            $data2Commandes[] = $commandeRepository->countSumOrdersByMonth($year, $month);
            $dataLivraisons[] = $livraisonRepository->countLivraisonsByMonth($year, $month);
            $dataProductions[] = $productionRepository->countProductionsByMonth($year, $month);
            $dataDepenses[] = $depenseRepository->countSumDepensesByMonth($year, $month);
            $dataTransactions[] = $tfr->countSumTransactionsByMonth($year, $month);
        }
        
        // Construire le graphique
        $moisNoms = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];
        $moisLabels = [];
        foreach ($months as $month) {
            $moisLabels[] = $moisNoms[$month];
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $moisLabels, // Les labels deviennent les mois
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'backgroundColor' => 'rgba(0, 255, 0, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $dataCommandes, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Productions',
                    'backgroundColor' => 'rgba(0, 0, 255, 0.4)',
                    'borderColor' => 'rgba(70, 123, 235, 1)',
                    'data' => $dataProductions, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Livraisons',
                    'backgroundColor' => 'rgba(255, 0, 0, 0.4)',
                    'borderColor' => 'rgba(255, 0, 0, 1)',
                    'data' => $dataLivraisons, // Les données dynamiques
                    'tension' => 0.4,
                ],
                
            ],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 10, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);
        $chart2 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart2->setData([
            'labels' => $moisLabels, // Les labels deviennent les mois
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $data2Commandes, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Dépenses',
                    'backgroundColor' => 'rgba(70, 123, 235, 0.4)',
                    'borderColor' => 'rgba(255, 0, 0, 0.4)',
                    'data' => $dataDepenses, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Transaction fournisseurs',
                    'backgroundColor' => 'rgba(54, 29, 200, 0.4)',
                    'borderColor' => 'rgba(54, 29, 200, 1)',
                    'data' => $dataTransactions, // Les données dynamiques
                    'tension' => 0.4,
                ],
                
            ],
        ]);
        $chart2->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 50000, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);
        
        /*** */
        $years = range($year - 4, $year);
        $data3Commandes = [];
        $data3Depenses = [];
        $data3Transactions = [];
        foreach ($years as $year) {
            $data3Commandes[] = $commandeRepository->countSumOrdersByYear($year);
            $data3Depenses[] = $depenseRepository->countSumDepensesByYear($year);
            $data3Transactions[] = $tfr->countSumTransactionsByYear($year);
        }
        $chart3 = $chartBuilder->createChart(Chart::TYPE_POLAR_AREA);
        $chart3->setData([
            'labels' => $years, // Les labels deviennent les mois
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $data3Commandes, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Dépenses',
                    'backgroundColor' => 'rgba(70, 123, 235, 0.4)',
                    'borderColor' => 'rgba(70, 123, 235, 1)',
                    'data' => $data3Depenses, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Transactions',
                    'backgroundColor' => 'rgba(54, 29, 200, 0.4)',
                    'borderColor' => 'rgba(54, 29, 200, 1)',
                    'data' => $data3Transactions, // Les données dynamiques
                    'tension' => 0.4,
                ],
                
            ],
        ]);
        $chart3->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 1, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);

        return $this->render('admin/accueil/index.html.twig', [
            'clients' => $clients,
            'commandes' => $commandes,
            'employes' => $employes,
            'caisses' => $caisses,
            'chart' => $chart,
            'chart2' => $chart2,
            'chart3' => $chart3,
        ]);
    }
}
