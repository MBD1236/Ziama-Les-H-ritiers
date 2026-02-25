<?php

namespace App\Repository;

use App\Entity\Bobine;
use App\Entity\LigneVente;
use App\Entity\MouvementBobine;
use App\Entity\MouvementStock;
use App\Entity\Produit;
use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<MouvementStock>
 */
class MouvementStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, MouvementStock::class);
    }

    public function paginateMouvementsStock(Produit $produit, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('m')->where('m.produit = :produit')->setParameter('produit', $produit)->orderBy('m.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateMouvementStockWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('m')
                ->innerJoin('m.produit', 'p')
                ->where('m.typeMouvement LIKE :query OR m.date LIKE :query OR m.quantite LIKE :query OR p.libelle LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->orderBy('m.id', 'DESC'),
            $page,
            15
        );
    }

    public function mouvementProduits(Produit $produit)
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.produit', 'p')
            ->where('p = :produit')
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getResult();
    }

    public function findMouvementsByVente(Vente $vente): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.typeMouvement LIKE :type')
            ->setParameter('type', 'Vente#' . $vente->getId() . '%')
            ->getQuery()
            ->getResult();
    }
}
