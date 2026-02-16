<?php

namespace App\Controller;

use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Repository\DepenseRepository;
use App\Repository\LivraisonRepository;
use App\Repository\ProduitRepository;
use App\Repository\TransactionFournisseurRepository;
use App\Repository\UserRepository;
use App\Repository\VenteRepository;
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
    public function index(ClientRepository $clientRepository, VenteRepository $venteRepository,
    CaisseRepository $caisseRepository, UserRepository $userRepository, ChartBuilderInterface $chartBuilder,
    LivraisonRepository $livraisonRepository, ProduitRepository $produitRepository,
    DepenseRepository $depenseRepository, TransactionFournisseurRepository $tfr): Response
    {
        /**** */
        $currentYear = new DateTime();
        $year = $currentYear->format('Y');
        $currentMonth = (int)$currentYear->format('m');
        $day = $currentYear->format('d');

        if ($currentMonth == 1){
            $months = [9, 10, 11, 12, 1];
            $lastYear = (int)$year - 1;
        }
        elseif ($currentMonth == 2){
            $months = [10, 11, 12, 1, 2];
            $lastYear = (int)$year - 1;
        }
        elseif ($currentMonth == 3){
            $months = [11, 12, 1, 2, 3];
            $lastYear = (int)$year - 1;
        }
        elseif ($currentMonth == 4){
            $months = [12, 1, 2, 3, 4];
            $lastYear = (int)$year - 1;
        }
        else
            $months = range($currentMonth - 4, $currentMonth);

        /** Nombre de livraisons */
        $livraisons = $livraisonRepository->countAll();
        $nombreLivraisonParJour = $livraisonRepository->countLivraisonsByDay($year, $currentMonth,$day);
        /** Etat de la caisse */
        $caisses = $caisseRepository->getEtatCaisse();
        /** Etat des ventes */
        $ventes = $venteRepository->countAll();
        $nombreVenteParJour = $venteRepository->countOrdersByDay($year, $currentMonth,$day);
        /** Nombre d'employés */
        $produits = $produitRepository->countAll();
        // $nombreProductionParJour = $productionRepository->countProductionsByDay($year, $currentMonth, $day);
        
        $nombreVentesParMois = [];
        $nombreLivraisonsParMois = [];
        $sommeDepensesParMois = [];
        $sommeTransactionsParMois = [];


        
        foreach ($months as $month) {
            if ($month > $currentMonth) {
                // Mois qui appartiennent à l'année précédente
                $nombreVentesParMois[] = $venteRepository->countOrdersByMonth($lastYear, $month);
                $nombreLivraisonsParMois[] = $livraisonRepository->countLivraisonsByMonth($lastYear, $month);
                $sommeDepensesParMois[] = $depenseRepository->countSumDepensesByMonth($lastYear, $month);
                $sommeTransactionsParMois[] = $tfr->countSumTransactionsByMonth($lastYear, $month);
            } else {
                // Mois qui appartiennent à l'année en cours
                $nombreVentesParMois[] = $venteRepository->countOrdersByMonth($year, $month);
                $nombreLivraisonsParMois[] = $livraisonRepository->countLivraisonsByMonth($year, $month);
                $sommeDepensesParMois[] = $depenseRepository->countSumDepensesByMonth($year, $month);
                $sommeTransactionsParMois[] = $tfr->countSumTransactionsByMonth($year, $month);
            }
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
                    'label' => 'Ventes',
                    'backgroundColor' => 'rgba(0, 255, 0, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $nombreVentesParMois, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Livraisons',
                    'backgroundColor' => 'rgba(255, 0, 0, 0.4)',
                    'borderColor' => 'rgba(255, 0, 0, 1)',
                    'data' => $nombreLivraisonsParMois, // Les données dynamiques
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
                    'label' => 'Ventes',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $nombreVentesParMois, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Dépenses',
                    'backgroundColor' => 'rgba(70, 123, 235, 0.4)',
                    'borderColor' => 'rgba(255, 0, 0, 0.4)',
                    'data' => $sommeDepensesParMois, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Transaction fournisseurs',
                    'backgroundColor' => 'rgba(54, 29, 200, 0.4)',
                    'borderColor' => 'rgba(54, 29, 200, 1)',
                    'data' => $sommeTransactionsParMois, // Les données dynamiques
                    'tension' => 0.4,
                ],
                
            ],
        ]);
        $chart2->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 100000, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);
        
        /*** */
        $years = range($year - 4, $year);
        $sommeVentesParAnnee = [];
        $sommeDepensesParAnnee = [];
        $sommeTransactionsParAnnee = [];
        foreach ($years as $year) {
            $sommeVentesParAnnee[] = $venteRepository->countSumOrdersByYear($year);
            $sommeDepensesParAnnee[] = $depenseRepository->countSumDepensesByYear($year);
            $sommeTransactionsParAnnee[] = $tfr->countSumTransactionsByYear($year);
        }
        $chart3 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart3->setData([
            'labels' => $years, // Les labels deviennent les mois
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $sommeVentesParAnnee, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Dépenses',
                    'backgroundColor' => 'rgba(70, 123, 235, 0.4)',
                    'borderColor' => 'rgba(70, 123, 235, 1)',
                    'data' => $sommeDepensesParAnnee, // Les données dynamiques
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Transactions',
                    'backgroundColor' => 'rgba(54, 29, 200, 0.4)',
                    'borderColor' => 'rgba(54, 29, 200, 1)',
                    'data' => $sommeTransactionsParAnnee, // Les données dynamiques
                    'tension' => 0.4,
                ],
                
            ],
        ]);
        $chart3->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 100000, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);

        /** Recuperer seulement les utilisateurs qui ont le role ROLE_LIVREUR */
        $utilisateurs = $userRepository->findAll();
        $em = [];
        foreach ($utilisateurs as $utilisateur) {
            $verif = in_array("ROLE_LIVREUR", $utilisateur->getRoles());
            if ($verif)
                $em [] = $utilisateur;
        }
        $nombreLivraisons = [];
        $nombreLivraisonsParAn = [];
        foreach ($em as $e) {
            $nombreLivraisons[] = $livraisonRepository->countLivraisonUserByMonth($e, $year, $month);
            $nombreLivraisonsParAn[] = $livraisonRepository->countLivraisonUserByYear($e, $year);
        }
        $users = [];
        foreach ($em as $u) {
            $users [] = $u->getUsername();
        }
        $nomMois = $moisNoms[$month];
        
        $chart4 = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart4->setData([
            'labels' => $users, // Les labels deviennent les mois
            'datasets' => [
                [
                    'label' => $nomMois,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.4)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $nombreLivraisons, // Les données dynamiques
                    'tension' => 0.4,
                ],
            ],
        ]);
        $chart4->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 10, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);
        $chart5 = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart5->setData([
            'labels' => $users, // Les labels deviennent les mois
            'datasets' => [
                [
                    'label' => $year,
                    'backgroundColor' => 'rgba(255, 0, 0, 0.4)',
                    'borderColor' => 'rgba(255, 0, 0, 1)',
                    'data' => $nombreLivraisonsParAn, // Les données dynamiques
                    'tension' => 0.4,
                ],
            ],
        ]);
        $chart5->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 10, // Affiche les ticks par incréments de 1
                    ],
                ],
            ],
        ]);


        return $this->render('admin/accueil/index.html.twig', [
            'livraisons' => $livraisons,
            'nombreLivraisonParJour' => $nombreLivraisonParJour,
            'ventes' => $ventes,
            'produits' => $produits,
            'nombreCommandeParJour' => $nombreVenteParJour,
            'caisses' => $caisses,
            'chart' => $chart,
            'chart2' => $chart2,
            'chart3' => $chart3,
            'chart4' => $chart4,
            'chart5' => $chart5,
        ]);
    }
}
