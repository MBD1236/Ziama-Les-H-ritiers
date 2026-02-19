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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/accueil')]
class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_admin_accueil_index', methods: ['GET'])]
    public function index(
        Request $request,
        ClientRepository $clientRepository,
        VenteRepository $venteRepository,
        CaisseRepository $caisseRepository,
        UserRepository $userRepository,
        ChartBuilderInterface $chartBuilder,
        LivraisonRepository $livraisonRepository,
        ProduitRepository $produitRepository,
        DepenseRepository $depenseRepository,
        TransactionFournisseurRepository $tfr
    ): Response {

        // Récupère et efface le message de rupture
        $ruptureMsg = $request->getSession()->get('rupture_notification');
        if ($ruptureMsg) {
            $request->getSession()->remove('rupture_notification'); //
            $this->addFlash('warning', $ruptureMsg);
        }

        /* Dates courantes  */
        $now          = new DateTime();
        $year         = (int) $now->format('Y');
        $currentMonth = (int) $now->format('m');
        $day          = (int) $now->format('d');

        /* Période filtrée (formulaire date début/fin */
        $dateDebutStr = $request->query->get('dateDebut', $now->format('Y-m-01'));
        $dateFinStr   = $request->query->get('dateFin',   $now->format('Y-m-d'));
        $dateDebut    = new DateTime($dateDebutStr);
        $dateFin      = (new DateTime($dateFinStr))->setTime(23, 59, 59);

        /* KPI cartes */
        $livraisons             = $livraisonRepository->countAll();
        $nombreLivraisonParJour = $livraisonRepository->countLivraisonsByDay($year, $currentMonth, $day);
        $caisses                = $caisseRepository->getEtatCaisse();
        $ventes                 = $venteRepository->countAll();
        $nombreVenteParJour     = $venteRepository->countOrdersByDay($year, $currentMonth, $day);
        $produits               = $produitRepository->countAll();
        $nbProduitsEnRupture    = $produitRepository->countProduitsEnRupture();
        $produitsEnRupture      = $produitRepository->getProduitsEnRupture();

        /* CA, Bénéfice brut, Bénéfice net (période filtrée) */
        $chiffreAffaires  = $venteRepository->getChiffreAffairesPeriode($dateDebut, $dateFin);
        $beneficeBrut     = $venteRepository->getBeneficeBrutPeriode($dateDebut, $dateFin);
        $totalCharges     = $depenseRepository->getSommeDepensesPeriode($dateDebut, $dateFin);
        $beneficeNet      = $beneficeBrut - $totalCharges;

        /* 5 derniers mois glissants  */
        $lastYear = null;
        if ($currentMonth <= 4) {
            $months   = array_merge(range($currentMonth + 8, 12), range(1, $currentMonth));
            $lastYear = $year - 1;
        } else {
            $months = range($currentMonth - 4, $currentMonth);
        }

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

        $moisLabels           = [];
        $nombreVentesParMois  = [];
        $sommeDepensesParMois = [];

        foreach ($months as $month) {
            $moisLabels[] = $moisNoms[$month];
            $yr = ($lastYear && $month > $currentMonth) ? $lastYear : $year;
            $nombreVentesParMois[]  = $venteRepository->countOrdersByMonth($yr, $month);
            $sommeDepensesParMois[] = $depenseRepository->countSumDepensesByMonth($yr, $month);
        }

        /* Chart 1 : Nombre de ventes par mois (bar)  */
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels'   => $moisLabels,
            'datasets' => [[
                'label'           => 'Nombre de ventes',
                'backgroundColor' => 'rgba(0, 200, 83, 0.5)',
                'borderColor'     => 'rgba(0, 200, 83, 1)',
                'data'            => $nombreVentesParMois,
            ]],
        ]);
        $chart->setOptions(['maintainAspectRatio' => false]);

        /* ── Chart 2 : CA vs Dépenses par mois (line) ───────────── */
        $sommeVentesParMois = [];
        foreach ($months as $month) {
            $yr = ($lastYear && $month > $currentMonth) ? $lastYear : $year;
            $sommeVentesParMois[] = $venteRepository->countSumOrdersByMonth($yr, $month);
        }

        $chart2 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart2->setData([
            'labels'   => $moisLabels,
            'datasets' => [
                [
                    'label'           => 'CA (GNF)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.3)',
                    'borderColor'     => 'rgba(54, 162, 235, 1)',
                    'data'            => $sommeVentesParMois,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Dépenses (GNF)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.3)',
                    'borderColor'     => 'rgba(255, 99, 132, 1)',
                    'data'            => $sommeDepensesParMois,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
            ],
        ]);
        $chart2->setOptions([
            'maintainAspectRatio' => false,
            'scales' => ['y' => ['ticks' => ['stepSize' => 100000]]],
        ]);

        /* ── Chart 3 : Vue annuelle (5 ans) ─────────────────────── */
        $years                 = range($year - 4, $year);
        $sommeVentesParAnnee   = [];
        $sommeDepensesParAnnee = [];
        foreach ($years as $y) {
            $sommeVentesParAnnee[]   = $venteRepository->countSumOrdersByYear($y);
            $sommeDepensesParAnnee[] = $depenseRepository->countSumDepensesByYear($y);
        }

        $chart3 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart3->setData([
            'labels'   => $years,
            'datasets' => [
                [
                    'label'           => 'CA annuel (GNF)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.4)',
                    'borderColor'     => 'rgba(54, 162, 235, 1)',
                    'data'            => $sommeVentesParAnnee,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Dépenses annuelles (GNF)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.4)',
                    'borderColor'     => 'rgba(255, 99, 132, 1)',
                    'data'            => $sommeDepensesParAnnee,
                    'tension'         => 0.4,
                ],
            ],
        ]);
        $chart3->setOptions([
            'maintainAspectRatio' => false,
            'scales' => ['y' => ['ticks' => ['stepSize' => 500000]]],
        ]);

        /* ── Chart 4 : Top 5 produits les plus vendus (bar horizontal) */
        $topProduits       = $venteRepository->getTopProduits(5);
        $topProduitsLabels = array_column($topProduits, 'produit');
        $topProduitsData   = array_column($topProduits, 'totalQuantite');

        $chart4 = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart4->setData([
            'labels'   => $topProduitsLabels,
            'datasets' => [[
                'label'           => 'Quantité vendue',
                'backgroundColor' => [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                ],
                'borderColor'     => [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                ],
                'borderWidth'     => 1,
                'data'            => $topProduitsData,
            ]],
        ]);
        $chart4->setOptions([
            'maintainAspectRatio' => false,
            'indexAxis'           => 'y',  // Horizontal
        ]);

        /* ── Chart 5 : Répartition des charges par type (doughnut) ─ */
        $depensesParType       = $depenseRepository->getDepensesParTypeParAnnee($year);
        $depensesLabels        = array_column($depensesParType, 'type');
        $depensesData          = array_column($depensesParType, 'total');
        $depenseColors = [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)',
        ];

        $chart5 = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart5->setData([
            'labels'   => $depensesLabels,
            'datasets' => [[
                'label'           => 'Charges par type',
                'backgroundColor' => array_slice($depenseColors, 0, count($depensesLabels)),
                'data'            => $depensesData,
            ]],
        ]);
        $chart5->setOptions([
            'maintainAspectRatio' => false,
            'plugins'             => ['legend' => ['position' => 'right']],
        ]);

        /* ── Chart 6 : Ventes par catégorie (pie) ───────────────── */
        $ventesParCategorie = $venteRepository->getVentesParMoisEtCategorie($year);

        // Agréger par catégorie (somme sur toute l'année)
        $categorieMap = [];
        foreach ($ventesParCategorie as $row) {
            $cat = $row['categorie'] ?? 'Sans catégorie';
            $categorieMap[$cat] = ($categorieMap[$cat] ?? 0) + $row['totalCA'];
        }

        $chart6 = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart6->setData([
            'labels'   => array_keys($categorieMap),
            'datasets' => [[
                'label'           => 'CA par catégorie',
                'backgroundColor' => array_slice($depenseColors, 0, count($categorieMap)),
                'data'            => array_values($categorieMap),
            ]],
        ]);
        $chart6->setOptions([
            'maintainAspectRatio' => false,
            'plugins'             => ['legend' => ['position' => 'right']],
        ]);

        /* ── Livreurs ────────────────────────────────────────────── */
        $utilisateurs = $userRepository->findAll();
        $livreurs     = array_filter($utilisateurs, fn($u) => in_array('ROLE_LIVREUR', $u->getRoles()));
        $livreurs     = array_values($livreurs);

        $livreursNoms           = array_map(fn($u) => $u->getUsername(), $livreurs);
        $nombreLivraisons       = [];
        $nombreLivraisonsParAn  = [];
        foreach ($livreurs as $e) {
            $nombreLivraisons[]      = $livraisonRepository->countLivraisonUserByMonth($e, $year, $currentMonth);
            $nombreLivraisonsParAn[] = $livraisonRepository->countLivraisonUserByYear($e, $year);
        }

        $chart7 = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart7->setData([
            'labels'   => $livreursNoms,
            'datasets' => [
                [
                    'label'           => $moisNoms[$currentMonth],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor'     => 'rgba(54, 162, 235, 1)',
                    'data'            => $nombreLivraisons,
                ],
                [
                    'label'           => 'Année ' . $year,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor'     => 'rgba(255, 99, 132, 1)',
                    'data'            => $nombreLivraisonsParAn,
                ],
            ],
        ]);
        $chart7->setOptions([
            'maintainAspectRatio' => false,
            'scales'              => ['y' => ['ticks' => ['stepSize' => 10]]],
        ]);

        return $this->render('admin/accueil/index.html.twig', [
            // KPI
            'livraisons'             => $livraisons,
            'nombreLivraisonParJour' => $nombreLivraisonParJour,
            'ventes'                 => $ventes,
            'produits'               => $produits,
            'nombreCommandeParJour'  => $nombreVenteParJour,
            'caisses'                => $caisses,
            'nbProduitsEnRupture'    => $nbProduitsEnRupture,
            'produitsEnRupture'      => $produitsEnRupture,

            // Financiers (période filtrée)
            'chiffreAffaires'        => $chiffreAffaires,
            'beneficeBrut'           => $beneficeBrut,
            'beneficeNet'            => $beneficeNet,
            'totalCharges'           => $totalCharges,
            'dateDebut'              => $dateDebutStr,
            'dateFin'                => $dateFinStr,

            // Graphiques
            'chart'                  => $chart,
            'chart2'                 => $chart2,
            'chart3'                 => $chart3,
            'chart4'                 => $chart4,
            'chart5'                 => $chart5,
            'chart6'                 => $chart6,
            'chart7'                 => $chart7,

            // Top produits (tableau)
            'topProduits'            => $topProduits,
        ]);
    }
}
