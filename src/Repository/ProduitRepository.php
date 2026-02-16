<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Produit::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateProduits(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')->orderBy('p.id', 'DESC'),
            $page,
            15
        );
    }

    public function paginateProduitsWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('p')
            ->where('p.nom LIKE :query OR p.prixAchat LIKE :query')->orderBy('p.id', 'DESC')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }

}