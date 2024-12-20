<?php

namespace App\Repository;

use App\Entity\ProductionEmploye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<ProductionEmploye>
 */
class ProductionEmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, ProductionEmploye::class);
    }


    public function paginateProductionEmployesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->innerJoin('p.production', 'production')
            ->where('u.username LIKE :query OR production.codeProduction LIKE :query')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            2
        );
    }
    public function paginateProductionEmployes(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p'),
            $page,
            2
        );
    }
}
