<?php

namespace App\Repository;

use App\Entity\Fournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Fournisseur>
 */
class FournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Fournisseur::class);
    }


    public function paginateFournisseursWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('f')
                ->where('f.nom LIKE :query OR f.adresse LIKE :query OR f.telephone LIKE :query OR f.email LIKE :query OR f.produitFourni LIKE :query')
                ->setParameter('query', '%'.$query.'%') 
            ,
            $page,
            2
        );
    }

    public function paginateFournisseurs(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('f'),
            $page,
            2
        );
    }
}
