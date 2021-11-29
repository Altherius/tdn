<?php

namespace App\Elo;

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
}