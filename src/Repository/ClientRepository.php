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
            $this->createQueryBuilder('c')->orderBy('c.id', 'DESC'),
            $page,
            15
        );
    }
    public function paginateWithSearch(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
                ->where('c.nom LIKE :query OR c.adresse LIKE :query OR c.telephone LIKE :query OR c.typeClient LIKE :query')->orderBy('c.id', 'DESC')
                ->setParameter('query', '%' . $query . '%'),
            $page,
            15
        );
    }

    /** Toutes les ventes d'un client avec filtre optionnel par période */
    /** Toutes les ventes d'un client */
    public function getVentesClient(Client $client): array
    {
        try {
            return $this->createQueryBuilder('c')
                ->select('v')
                ->join('c.ventes', 'v')
                ->where('c.id = :id')
                ->setParameter('id', $client->getId())
                ->orderBy('v.dateVente', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Throwable $th) {
            return [];
        }
    }

    /** Montant total dépensé par un client (avec filtre optionnel) */
    public function getMontantTotalClient(Client $client, ?string $dateDebut = null, ?string $dateFin = null): float
    {
        $qb = $this->createQueryBuilder('c')
            ->select('SUM(lv.totalLigne)')
            ->join('c.ventes', 'v')
            ->join('v.lignes', 'lv')
            ->where('c.id = :id')
            ->setParameter('id', $client->getId());

        if ($dateDebut) {
            $qb->andWhere('v.dateVente >= :debut')
                ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
        }
        if ($dateFin) {
            $qb->andWhere('v.dateVente <= :fin')
                ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }

        try {
            return (float) ($qb->getQuery()->getSingleScalarResult() ?? 0);
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
