<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Facture::class);
    }


    public function paginateFactures(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('f'),
            $page,
            2
        );
    }

    public function paginateFacturesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('f')
            ->innerJoin('f.commande', 'c')
            ->where('f.codeFacture LIKE :query OR c.codeCommande LIKE :query OR f.statut LIKE :query OR f.montantRegle LIKE :query OR f.montantRestant LIKE :query OR f.codeFacture LIKE :query')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            2
        );
    }
}
