<?php

namespace App\Command;

use App\Elo\EloCalculator;
use App\Entity\FootballMatch;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EloCalculateCommand extends Command
{
    protected static $defaultName = 'app:elo:calculate';

    private EntityManagerInterface $manager;
    private EloCalculator $eloCalculator;

    public function __construct(EntityManagerInterface $manager, EloCalculator $eloCalculator)
    {
        $this->manager = $manager;
        $this->eloCalculator = $eloCalculator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command reset elo and recalculate it with given matches.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Réinitialisation du classement.</info>');
        $teams = $this->manager->getRepository(Team::class)->findAll();
        $matches = $this->manager->getRepository(FootballMatch::class)->findAll();
        foreach ($teams as $team) {
            $team->setRating(1000);
        }
        $this->manager->flush();
        $output->writeln('<info>Classement réinitialisé.</info>');
        $output->writeln('<info>Simulation des matchs.</info>');

        /** @var FootballMatch $match */
        foreach ($matches as $match) {

            $eloDiff = $this->eloCalculator->getEloEvolution($match->getHostingTeam()?->getRating(), $match->getReceivingTeam()?->getRating(), $match->getHostingTeamResult());
            $output->writeln("<comment>" . $match->toStringwithElo() . " : ($eloDiff|".-$eloDiff .")</comment>");

            $match->getHostingTeam()?->addRating($eloDiff);
            $match->getReceivingTeam()?->addRating(-$eloDiff);

            $this->manager->flush();
        }

        $output->writeln('<info>Simulation des matchs terminée.</info>');

        return Command::SUCCESS;
    }
}