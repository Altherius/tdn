<?php

namespace App\DataFixtures;

use App\Entity\FootballMatch;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MatchFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private TeamRepository $matchRepository,
        private TournamentRepository $tournamentRepository
    ) {}

    public function load(ObjectManager $manager): void
    {
        $teams = $this->matchRepository->findAll();
        $tournament = $this->tournamentRepository->findAll()[0];

        $team1 = $teams[0];
        $team2 = $teams[1];

        for ($i = 0 ; $i < 4 ; ++$i) {
            $match = (new FootballMatch())
                ->setHostingTeam($team1)
                ->setReceivingTeam($team2)
                ->setDescription('Aller X-Y Retour X-Y')
                ->setWinner($team1)
                ->setLoser($team2)
                ->setHostingTeamScore(3)
                ->setReceivingTeamScore(1)
                ->setTournament($tournament)
            ;

            $manager->persist($match);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TeamFixtures::class, TournamentFixtures::class];
    }
}
