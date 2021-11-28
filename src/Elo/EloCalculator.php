<?php

namespace App\Elo;

class EloCalculator
{
    public const LOSE = 0;
    public const WIN = 1;
    public const DRAW = 0.5;

    public function getEloEvolution(int $rating, int $opponentRating, float $result): int
    {
        $eloDiff = $rating - $opponentRating;
        if ($eloDiff > 400) {
            $eloDiff = 400;
        }
        $winProbability = 1 / (1 + (10 ** (-$eloDiff / 400)));

        return 20 * ($result - $winProbability);
    }
}