<?php

namespace App\Repository;

use App\Entity\Bobine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Bobine>
 */
class BobineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Bobine::class);
    }

    public function paginateBobines(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('b')->orderBy('b.id', 'DESC'),
            $page,
            15
        );
    }

    public function paginateBobinesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('b')
            ->where('b.type LIKE :query OR b.prixUnitaire LIKE :query')->orderBy('b.id', 'DESC')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }

}
