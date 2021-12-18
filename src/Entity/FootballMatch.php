<?php

namespace App\Entity;

use App\Elo\EloCalculator;
use App\Repository\FootballMatchRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=FootballMatchRepository::class)
 */
class FootballMatch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="matchesHosting")
     * @ORM\JoinColumn(nullable=false)
     */
    private Team $hostingTeam;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="matchesReceiving")
     * @ORM\JoinColumn(nullable=false)
     */
    private Team $receivingTeam;

    /**
     * @ORM\Column(type="integer")
     */
    private int $hostingTeamScore = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $receivingTeamScore = 0;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="footballMatches")
     * @ORM\JoinColumn(nullable=false)
     */
    private Tournament $tournament;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="victories")
     */
    private ?Team $winner;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="defeats")
     */
    private ?Team $loser;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="matchesWonAtPenalties")
     */
    private ?Team $penaltiesWinner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHostingTeam(): ?Team
    {
        return $this->hostingTeam;
    }

    public function setHostingTeam(Team $hostingTeam): self
    {
        $this->hostingTeam = $hostingTeam;

        return $this;
    }

    public function getReceivingTeam(): ?Team
    {
        return $this->receivingTeam;
    }

    public function setReceivingTeam(Team $receivingTeam): self
    {
        $this->receivingTeam = $receivingTeam;

        return $this;
    }

    public function getHostingTeamScore(): ?int
    {
        return $this->hostingTeamScore;
    }

    public function setHostingTeamScore(int $hostingTeamScore): self
    {
        $this->hostingTeamScore = $hostingTeamScore;

        return $this;
    }

    public function getReceivingTeamScore(): ?int
    {
        return $this->receivingTeamScore;
    }

    public function setReceivingTeamScore(int $receivingTeamScore): self
    {
        $this->receivingTeamScore = $receivingTeamScore;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getLoser(): ?Team
    {
        return $this->loser;
    }

    public function setLoser(?Team $loser): self
    {
        $this->loser = $loser;

        return $this;
    }

    #[Pure] public function getHostingTeamResult(): float
    {
        if ($this->getWinner() === $this->getHostingTeam()) {
            return EloCalculator::WIN;
        }

        if ($this->getLoser() === $this->getHostingTeam()) {
            return EloCalculator::LOSE;
        }

        return EloCalculator::DRAW;
    }

    #[Pure] public function getGoalsDiff(): int
    {
        return abs($this->getHostingTeamScore() - $this->getReceivingTeamScore());
    }

    #[Pure] public function __toString(): string
    {
        return $this->getHostingTeam() . ' ' . $this->getHostingTeamScore() . '-' . $this->getReceivingTeamScore() . ' ' . $this->getReceivingTeam();
    }

    public function toStringWithElo(): string
    {
        return $this->getHostingTeam() . ' (' . $this->getHostingTeam()?->getRating() . ') ' . $this->getHostingTeamScore() . '-' .
               $this->getReceivingTeamScore() . ' ' . $this->getReceivingTeam() . ' (' . $this->getReceivingTeam()?->getRating() . ')';
    }

    public function getPenaltiesWinner(): ?Team
    {
        return $this->penaltiesWinner;
    }

    public function setPenaltiesWinner(?Team $penaltiesWinner): self
    {
        $this->penaltiesWinner = $penaltiesWinner;

        return $this;
    }
}
