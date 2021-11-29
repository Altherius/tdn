<?php

namespace App\Command;

use App\Entity\FootballMatch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MatchResultCommand extends Command
{
    protected static $defaultName = 'app:match-result';

    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command correct match results in database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Correction des résultats des matchs.</info>');
        $matches = $this->manager->getRepository(FootballMatch::class)->findAll();

        /** @var FootballMatch $match */
        foreach ($matches as $match) {
            if ($match->getWinner() === null || $match->getLoser() === null) {
                if ($match->getHostingTeamScore() > $match->getReceivingTeamScore()) {
                    $match->setWinner($match->getHostingTeam());
                    $match->setLoser($match->getReceivingTeam());
                    $output->writeln("<comment>$match</comment> - Vainqueur : " . $match->getWinner());
                } else if ($match->getHostingTeamScore() < $match->getReceivingTeamScore()) {
                    $match->setWinner($match->getReceivingTeam());
                    $match->setLoser($match->getHostingTeam());
                    $output->writeln("<comment>$match</comment> - Vainqueur : " . $match->getWinner());
                }
            }
        }

        $this->manager->flush();
        $output->writeln('<info>Correction des résultats terminée.</info>');

        return Command::SUCCESS;
    }
}