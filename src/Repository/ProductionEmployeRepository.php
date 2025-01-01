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
            ->where('u.username LIKE :query OR production.codeProduction LIKE :query')->orderBy('p.id', 'DESC')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }
    public function paginateProductionEmployesWithSearch2($user, string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->innerJoin('p.production', 'production')
            ->where('p.user = :user')
            ->andWhere('u.username LIKE :query OR production.codeProduction LIKE :query')->orderBy('p.id', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }
    public function paginateProductionEmployes(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateProductionEmployes2($user, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.id', 'DESC'),
            $page,
            15
        );
    }
}
