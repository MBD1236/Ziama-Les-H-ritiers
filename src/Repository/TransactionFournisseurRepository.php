<?php

namespace App\Repository;

use App\Entity\TransactionFournisseur;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<TransactionFournisseur>
 */
class TransactionFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, TransactionFournisseur::class);
    }

    public function countSumTransactionsByMonth($year, $month): int
    {
        
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        try {
            return  $this->createQueryBuilder('t')
            ->select('SUM(t.montant)')
            ->where('t.dateTransaction BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        } catch (\Throwable $th) {
            return 0;
        }
    }
    public function countSumTransactionsByYear($year): int
    {
        
        $startDate = new DateTime("$year-01-01 00:00:00");
        $endDate = new DateTime("$year-12-31 23:59:59");

        try {
            return  $this->createQueryBuilder('t')
            ->select('SUM(t.montant)')
            ->where('t.dateTransaction BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        } catch (\Throwable $th) {
            return 0;
        }
    }


    public function paginateTransactionFournisseursWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('t')
            ->innerJoin('t.fournisseur', 'f')
            ->where('f.nom LIKE :query OR t.modePaiement LIKE :query OR t.description LIKE :query OR t.montant LIKE :query OR t.dateTransaction LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ,
            $page,
            2
        );
    }
    public function paginateTransactionFournisseurs(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('t'),
            $page,
            2
        );
    }
}
