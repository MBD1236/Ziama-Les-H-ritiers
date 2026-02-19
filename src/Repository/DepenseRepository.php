<?php

namespace App\Repository;

use App\Entity\Depense;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpParser\Node\Stmt\TryCatch;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Depense::class);
    }

    /** Somme des dépenses par mois */
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

    /** Somme des dépenses par année */
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

    /** Somme des dépenses sur une période */
    public function getSommeDepensesPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): float
    {
        try {
            $result = $this->createQueryBuilder('d')
                ->select('SUM(d.montant)')
                ->where('d.dateDepense >= :debut')
                ->andWhere('d.dateDepense <= :fin')
                ->setParameter('debut', $debut)
                ->setParameter('fin', $fin)
                ->getQuery()->getSingleScalarResult();

            return (float) ($result ?? 0);
        } catch (\Throwable $th) {
            return 0;
        }
    }


    /** Total des charges regroupées par type/catégorie */
    public function getDepensesParType(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        try {
            return $this->createQueryBuilder('d')
                ->select('d.type, SUM(d.montant) as total')
                ->where('d.dateDepense >= :debut')
                ->andWhere('d.dateDepense <= :fin')
                ->setParameter('debut', $debut)
                ->setParameter('fin', $fin)
                ->groupBy('d.type')
                ->orderBy('total', 'DESC')
                ->getQuery()->getArrayResult();
        } catch (\Throwable $th) {
            return [];
        }
    }

    /** Dépenses par type sur toute l'année */
    /** Dépenses par type sur toute l'année */
    public function getDepensesParTypeParAnnee(string $year): array
    {
        $startDate = new \DateTime("$year-01-01 00:00:00");
        $endDate   = new \DateTime("$year-12-31 23:59:59");

        try {
            return $this->createQueryBuilder('d')
                ->select('d.type, SUM(d.montant) as total')
                ->where('d.dateDepense BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->groupBy('d.type')
                ->orderBy('total', 'DESC')
                ->getQuery()->getArrayResult();
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function paginateDepenses(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('d')->orderBy('d.id', 'DESC'),
            $page,
            15
        );
    }

    public function paginateDepensesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('d')
                ->where('d.type LIKE :query OR d.description LIKE :query OR d.dateDepense LIKE :query OR d.montant LIKE :query')->orderBy('d.id', 'DESC')
                ->setParameter('query', '%' . $query . '%'),
            $page,
            2
        );
    }
}
