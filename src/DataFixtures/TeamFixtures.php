<?php

namespace App\DataFixtures;

use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $team1 = (new Team())
            ->setName('France')
            ->setRegion('Europe')
            ->setRating(1800)
            ->setColor('#4387cc')
            ->setCountryCode('fr')
        ;

        $team2 = (new Team())
            ->setName('Espagne')
            ->setRegion('Europe')
            ->setRating(1500)
            ->setColor('#dd5537')
            ->setCountryCode('es')
        ;

        $manager->persist($team1);
        $manager->persist($team2);

        $manager->flush();
    }
}
