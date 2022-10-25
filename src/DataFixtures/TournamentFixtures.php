<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TournamentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tournament = (new Tournament())
            ->setStartedAt(new DateTime())
            ->setName('Fête de Pornic')
            ->setEloMultiplier(1)
            ->setDescription('Super tournoi')
        ;

        $manager->persist($tournament);

        $manager->flush();
    }
}
