<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Vente;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class VenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Vente::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOrdersByDay($year, $month, $day): int
    {

        $startDate = new DateTime("$year-$month-$day 00:00:00");
        $endDate = new DateTime("$year-$month-$day 23:59:59");

        return  $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.dateVente BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOrdersByMonth($year, $month): int
    {
        // dd($year);
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        return  $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateVente BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countOrdersByYear($year): int
    {

        $startDate = new DateTime("$year-01-01 00:00:00");
        $endDate = new DateTime("$year-12-31 23:59:59");

        return  $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.dateVente BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Somme du CA par mois (via lignes de vente) */
    public function countSumOrdersByMonth($year, $month): int
    {

        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        try {
            return  $this->createQueryBuilder('v')
                ->select('SUM(l.totalLigne)')
                ->innerJoin('v.lignes', 'l')
                ->where('v.dateVente BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    /** Somme du CA par année */

    public function countSumOrdersByYear($year): int
    {

        $startDate = new DateTime("$year-01-01 00:00:00");
        $endDate = new DateTime("$year-12-31 23:59:59");

        try {
            return  $this->createQueryBuilder('v')
                ->select('SUM(l.totalLigne)')
                ->innerJoin('v.lignes', 'l')
                ->where('v.dateVente BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    /** CA total sur une période donnée */
    public function getChiffreAffairesPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): float
    {
        $result = $this->createQueryBuilder('v')
            ->select('SUM(lv.totalLigne)')
            ->join('v.lignes', 'lv')
            ->where('v.dateVente >= :debut')
            ->andWhere('v.dateVente <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /** Bénéfice brut sur une période (CA - coût d'achat des produits vendus) */
    public function getBeneficeBrutPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): float
    {
        $result = $this->createQueryBuilder('v')
            ->select('SUM(lv.totalLigne - (p.prixAchat * lv.quantite))')
            ->join('v.lignes', 'lv')
            ->join('lv.produit', 'p')
            ->where('v.dateVente >= :debut')
            ->andWhere('v.dateVente <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /** Top N produits les plus vendus (par quantité) */
    public function getTopProduits(int $limit = 5): array
    {
        try {
            return $this->createQueryBuilder('v')
                ->select('p.nom as produit, SUM(lv.quantite) as totalQuantite, SUM(lv.totalLigne) as totalCA')
                ->join('v.lignes', 'lv')
                ->join('lv.produit', 'p')
                ->groupBy('p.id')
                ->orderBy('totalQuantite', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()->getArrayResult();
        } catch (\Throwable $th) {
            return [];
        }
    }

    /** Ventes par mois et par catégorie (pour graphique) */
    public function getVentesParMoisEtCategorie(string $year): array
    {
        $startDate = new \DateTime("$year-01-01 00:00:00");
        $endDate   = new \DateTime("$year-12-31 23:59:59");

        try {
            return $this->createQueryBuilder('v')
                ->select('c.nom as categorie, SUM(lv.totalLigne) as totalCA')
                ->join('v.lignes', 'lv')
                ->join('lv.produit', 'p')
                ->leftJoin('p.categorie', 'c')  // leftJoin pour inclure les produits sans catégorie
                ->where('v.dateVente BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->groupBy('c.id')
                ->orderBy('totalCA', 'DESC')
                ->getQuery()->getArrayResult();
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function paginateVentes(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('v')->orderBy('v.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateVentesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('v')
                ->innerJoin('v.client', 'cl')
                ->innerJoin('v.lignes', 'l')
                ->innerJoin('l.produit', 'p')
                ->innerJoin('v.user', 'u')
                ->where('cl.nom LIKE :query OR v.dateVente LIKE :query OR p.nom LIKE :query OR u.username LIKE :query')->orderBy('v.id', 'DESC')
                ->setParameter('query', '%' . $query . '%'),
            $page,
            15
        );
    }

    public function findByClient(Client $client): array
    {
        try {
            return $this->createQueryBuilder('v')
                ->where('v.client = :client')
                ->setParameter('client', $client)
                ->orderBy('v.dateVente', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Throwable $th) {
            return [];
        }
    }
}
