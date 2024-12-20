<?php

namespace App\Repository;

use App\Entity\Production;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Production>
 */
class ProductionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Production::class);
    }

       /**
        * @return Production[] Returns an array of Production objects
        */
    public function findByStock(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nombrePack > :stock')
            ->setParameter('stock', 0)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countProductionsByMonth($year, $month): int
    {
        
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        return  $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.dateProduction BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateProductionsWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')->where('p.nombrePack > 0')
            ->where('p.codeProduction LIKE :query OR p.dateProduction LIKE :query')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            1
        );
    }
    public function paginateProductions(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')->where('p.nombrePack > 0'),
            $page,
            1
        );
    }

}
