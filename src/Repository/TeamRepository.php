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

    public function findRankings()
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->leftJoin('t.winnedTournaments', 'w')
            ->leftJoin('t.finalistTournaments', 'f')
            ->leftJoin('t.finalPhasesTournaments', 'fp')
            ->leftJoin('t.matchesReceiving', 'mr')
            ->leftJoin('t.matchesHosting', 'mh')
            ->addSelect('w')
            ->addSelect('f')
            ->addSelect('fp')
            ->addSelect('mr')
            ->addSelect('mh')
            ->orderBy('t.rating', 'desc');

        return $qb->getQuery()->getResult();
    }

    public function findPosition(int $rating): int
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->select("COUNT('t.id') as position")
            ->where("t.rating > :rating")
            ->setParameter('rating', $rating)
        ;

        $result = $qb->getQuery()->getOneOrNullResult();
        return 1 + $result['position'];
    }

    public function findWithStats()
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->leftJoin('t.matchesHosting', 'h')
            ->leftJoin('t.matchesReceiving', 'r')
            ->addSelect('h')
            ->addSelect('r');

        return $qb->getQuery()->getResult();
    }

}
