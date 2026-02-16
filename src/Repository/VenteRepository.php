<?php

namespace App\Repository;

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
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOrdersByDay($year, $month, $day): int
    {

        $startDate = new DateTime("$year-$month-$day 00:00:00");
        $endDate = new DateTime("$year-$month-$day 23:59:59");

        return  $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateVente BETWEEN :start AND :end')
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

        return  $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateVente BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function countSumOrdersByMonth($year, $month): int
    {
        
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        try {
            return  $this->createQueryBuilder('c')
            ->select('SUM(l.totalLigne)')
            ->innerJoin('c.lignes', 'l')
            ->where('c.dateVente BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }
    public function countSumOrdersByYear($year): int
    {
        
        $startDate = new DateTime("$year-01-01 00:00:00");
        $endDate = new DateTime("$year-12-31 23:59:59");

        try {
            return  $this->createQueryBuilder('c')
            ->select('SUM(l.totalLigne)')
            ->innerJoin('c.lignes', 'l')
            ->where('c.dateVente BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        } catch (\Throwable $th) {
            return 0;
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
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }
}
