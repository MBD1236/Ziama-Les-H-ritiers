<?php

namespace App\Repository;

use App\Entity\Commande;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Commande::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
   

    public function countOrdersByMonth($year, $month): int
    {
        
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        return  $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande BETWEEN :start AND :end')
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
            ->where('c.dateCommande BETWEEN :start AND :end')
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
            ->select('SUM(c.montantTotal)')
            ->where('c.dateCommande BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
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
            ->select('SUM(c.montantTotal)')
            ->where('c.dateCommande BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function paginateCommandes(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c'),
            $page,
            2
        );
    }
    public function paginateCommandesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
            ->innerJoin('c.client', 'cl')
            ->innerJoin('c.production', 'p')
            ->innerJoin('c.user', 'u')
            ->where('cl.nom LIKE :query OR c.dateCommande LIKE :query OR p.codeProduction LIKE :query OR u.username LIKE :query')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            2
        );
    }
}
