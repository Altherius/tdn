<?php

namespace App\Elo;

use App\Entity\FootballMatch;

class EloCalculator
{
    public const LOSE = 0;
    public const WIN = 1;
    public const DRAW = 0.5;

    public function getEloEvolution(int $rating, int $opponentRating, float $result): int
    {
        $k = 80;
        if ($rating > 1600 || $opponentRating > 1600) {
            $k = 40;
        }
        if ($rating > 2000 || $opponentRating > 2000) {
            $k = 20;
        }
        if ($rating > 2400 || $opponentRating > 2400) {
            $k = 10;
        }

        $eloDiff = $rating - $opponentRating;
        if ($eloDiff > 400) {
            $eloDiff = 400;
        }
        $winProbability = 1 / (1 + (10 ** (-$eloDiff / 400)));

        return $k * ($result - $winProbability);
    }

    public function getBaseEloEvolution(FootballMatch $match): float
    {
        $multiplier = $match->getTournament()?->getEloMultiplier() ?? 1.;
        return floor($multiplier * $this->getEloEvolution($match->getHostingTeam()?->getRating(), $match->getReceivingTeam()?->getRating(), $match->getHostingTeamResult()));
    }


    public function getEloEvolutionWithGoals(FootballMatch $match): int
    {
        $baseEloEvolution = $this->getEloEvolution($match->getHostingTeam()?->getRating(), $match->getReceivingTeam()?->getRating(), $match->getHostingTeamResult());
        $multiplier = $this->getEloMultiplier($match->getGoalsDiff());
        $tournamentMultiplier = $match->getTournament()?->getEloMultiplier() ?? 1.;

        return $baseEloEvolution * $multiplier * $tournamentMultiplier;
    }

    public function getEloMultiplier(int $goalsDiff): float
    {
        $multiplier = 1.0;
        switch ($goalsDiff) {
            case 0:
            case 1:
            case 2:
            case 3:
                break;
            case 4:
            case 5:
                $multiplier = 1.5;
                break;
            case 6:
            case 7:
                $multiplier = 1.75;
                break;
            default:
                $multiplier = 1.75 + (ceil((0.5 * ($goalsDiff - 7))) * 0.125);
        }

        return $multiplier;
    }

    public function getWinProbability(int $rating, int $opponentRating): float
    {
        $eloDiff = $rating - $opponentRating;
        if ($eloDiff > 400) {
            $eloDiff = 400;
        }
        return 100 * (1 / (1 + (10 ** (-$eloDiff / 400))));
    }
}