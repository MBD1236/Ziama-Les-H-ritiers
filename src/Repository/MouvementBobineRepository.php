<?php

namespace App\Repository;

use App\Entity\Bobine;
use App\Entity\MouvementBobine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<MouvementBobine>
 */
class MouvementBobineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, MouvementBobine::class);
    }

    public function paginateMouvementsBobine(Bobine $bobine,int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('m')->where('m.bobine = :bobine')->setParameter('bobine', $bobine),
            $page,
            1
        );
    }
}
