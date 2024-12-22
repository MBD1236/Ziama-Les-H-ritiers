<?php

namespace App\Repository;

use App\Entity\Caisse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Caisse>
 */
class CaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Caisse::class);
    }

    public function getEtatCaisse(): int
    {
        return $this->createQueryBuilder('c')
            ->select('SUM(CASE WHEN c.type = :encaissement THEN c.montant ELSE -c.montant END) AS solde')
            ->setParameter('encaissement', 'encaissement')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateCaisses(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')->orderBy('c.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateCaissesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
                ->where('c.type LIKE :query OR c.date LIKE :query OR c.montant LIKE :query')->orderBy('c.id', 'DESC')
                ->setParameter('query', '%'.$query.'%')
            ,
            $page,
            15
        );
    }
}
