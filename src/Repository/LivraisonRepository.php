<?php

namespace App\Repository;

use App\Entity\Livraison;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Livraison>
 */
class LivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Livraison::class);
    }


    public function countAll(): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countLivraisonsByDay($year, $month, $day): int
    {
        $startDate = new DateTime("$year-$month-$day 00:00:00");
        $endDate = new DateTime("$year-$month-$day 23:59:59");

        return  $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.dateLivraison BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countLivraisonsByMonth($year, $month): int
    {
        
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        return  $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.dateLivraison BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countLivraisonUserByMonth($user, $year, $month): int
    {
        $startDate = new DateTime("$year-$month-01 00:00:00");
        $endDate = new DateTime("$year-$month-31 23:59:59");

        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.user = :user')
            ->andWhere('l.dateLivraison BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countLivraisonUserByYear($user, $year): int
    {
        $startDate = new DateTime("$year-01-01 00:00:00");
        $endDate = new DateTime("$year-12-31 23:59:59");

        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.user = :user')
            ->andWhere('l.dateLivraison BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function paginateLivraisonsWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('l')
            ->innerJoin('l.commande', 'c')
            ->innerJoin('l.user', 'u')
            ->where('c.codeCommande LIKE :query OR u.username LIKE :query')->orderBy('l.id', 'DESC')
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }
    public function paginateLivraisonsWithSearch2($user, string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('l')
            ->innerJoin('l.commande', 'c')
            ->innerJoin('l.user', 'u')
            ->where('l.user = :user')
            ->andWhere('c.codeCommande LIKE :query OR u.username LIKE :query')->orderBy('l.id', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('query', '%'.$query.'%'),
            $page,
            15
        );
    }
    public function paginateLivraisons(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('l')->orderBy('l.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateLivraisons2($employe, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('l')
            ->where('l.user = :employe')
            ->setParameter('employe', $employe)
            ->orderBy('l.id', 'DESC'),
            $page,
            15
        );
    }
}
