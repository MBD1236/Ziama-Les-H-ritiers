<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Categorie>
 *
 * @method Categorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categorie[]    findAll()
 * @method Categorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Categorie::class);
    }

    public function paginateCategories(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')->orderBy('c.nom', 'ASC'),
            $page,
            15
        );
    }

    public function paginateCategoriesWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
                ->where('c.nom LIKE :query OR c.description LIKE :query')
                ->orderBy('c.nom', 'ASC')
                ->setParameter('query', '%' . $query . '%'),
            $page,
            15
        );
    }
}
