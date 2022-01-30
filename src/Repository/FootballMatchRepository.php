<?php

namespace App\Repository;

use App\Entity\FootballMatch;
use App\Entity\Team;
use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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

    public function findWithTeamQuery(Team $team): Query
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->orWhere('m.hostingTeam = :team')
            ->orWhere('m.receivingTeam = :team')
            ->setParameter('team', $team)
        ;

        return $qb->getQuery();
    }

    public function findTournamentLastMatch(Tournament $tournament)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.tournament = :tournament')
            ->andWhere('m.finalPhases = 1')
            ->andWhere('m.nextMatch IS NULL')
            ->setParameter('tournament', $tournament)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findWithTeam(Team $team)
    {
        $query = $this->findWithTeamQuery($team);
        return $query->getResult();
    }

    /**
     * @return FootballMatch[]
     */
    public function findMatchup(Team $team1, Team $team2): array
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->andWhere('m.hostingTeam IN (:matchup)')
            ->andWhere('m.receivingTeam IN (:matchup)')
            ->setParameter('matchup', [$team1, $team2])
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByHostingScoredGoals()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->join('m.hostingTeam', 't')
            ->select('t.id, t.name, (SUM(m.hostingTeamScore) / COUNT(m.hostingTeamScore / 2)) as scoredPerMatch')
            ->groupBy('m.hostingTeam');

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByHostingTakenGoals()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->join('m.hostingTeam', 't')
            ->select('t.id, t.name, (SUM(m.receivingTeamScore) / COUNT(m.receivingTeamScore / 2)) as scoredPerMatch')
            ->groupBy('m.hostingTeam');

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByReceivingTakenGoals()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->join('m.receivingTeam', 't')
            ->select('t.id, t.name, (SUM(m.hostingTeamScore) / COUNT(m.hostingTeamScore / 2)) as scoredPerMatch')
            ->groupBy('m.receivingTeam');

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByReceivingScoredGoals()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->join('m.receivingTeam', 't')
            ->select('t.id, t.name, (SUM(m.receivingTeamScore) / COUNT(m.receivingTeamScore / 2)) as scoredPerMatch')
            ->groupBy('m.receivingTeam');

        $query = $qb->getQuery();
        return $query->getResult();
    }
}
