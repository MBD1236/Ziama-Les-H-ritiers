<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Client::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function paginateClients(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c'),
            $page,
            2
        );
    }
    public function paginateWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
                ->where('c.nom LIKE :query OR c.adresse LIKE :query OR c.telephone LIKE :query OR c.typeClient LIKE :query')
                ->setParameter('query', '%'.$query.'%')
            ,
            $page,
            2
        );
    }
}
