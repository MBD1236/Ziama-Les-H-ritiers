<?php

namespace App\Repository;

use App\Entity\Depense;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Depense::class);
    }

    public function countSumDepensesByMonth($year, $month): int
    {
        
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        try {
            return  $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->where('d.dateDepense BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        } catch (\Throwable $th) {
            return 0;
        }
    }
    public function countSumDepensesByYear($year): int
    {
        
        $startDate = new DateTime("$year-01-01 00:00:00");
        $endDate = new DateTime("$year-12-31 23:59:59");

        try {
            return  $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->where('d.dateDepense BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function paginateDepenses(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('d'),
            $page,
            2
        );
    }

    public function paginateDepensesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('d')
            ->where('d.type LIKE :query OR d.description LIKE :query OR d.dateDepense LIKE :query OR d.montant LIKE :query')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            2
        );
    }
}
