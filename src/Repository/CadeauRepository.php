<?php

namespace App\Repository;

use App\Entity\Cadeau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Cadeau>
 */
class CadeauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Cadeau::class);
    }


    public function paginateCadeau(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')->orderBy('c.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateCadeauWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
                ->innerJoin('c.client', 'cl')
                ->innerJoin('c.production', 'p')
                ->where('cl.nom LIKE :query OR c.date LIKE :query OR p.codeProduction LIKE :query OR c.description LIKE :query')->orderBy('c.id', 'DESC')
                ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }
}
