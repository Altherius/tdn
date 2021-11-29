<?php

namespace App\Repository;

use App\Entity\FootballMatch;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FootballMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method FootballMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method FootballMatch[]    findAll()
 * @method FootballMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FootballMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FootballMatch::class);
    }

    public function findWithTeamQuery(Team $team)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->orWhere('m.hostingTeam = :team')
            ->orWhere('m.receivingTeam = :team')
            ->setParameter('team', $team)
        ;

        return $qb->getQuery();
    }

    public function findWithTeam(Team $team)
    {
        $query = $this->findWithTeamQuery($team);
        return $query->getResult();
    }
}
