<?php

namespace App\Repository;

use App\Entity\ReglementFacture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<ReglementFacture>
 */
class ReglementFactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, ReglementFacture::class);
    }


    public function paginateReglementFacturesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('r')
            ->where('r.modeReglement LIKE :query OR r.date LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ,
            $page,
            2
        );
    }
    public function paginateReglementFactures(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('r'),
            $page,
            2
        );
    }

}
