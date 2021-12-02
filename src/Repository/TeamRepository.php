<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findWithMatches()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->join('t.matchesHosting', 'mh')
            ->join('t.matchesReceiving', 'mr')
            ->addSelect('mh')
            ->addSelect('mr')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findRankings()
    {
        $qb = $this->createQueryBuilder('t');

        $qb->orderBy('t.rating', 'desc');

        return $qb->getQuery()->getResult();
    }
}
