<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 * @Gedmo\Loggable()
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $color;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned()
     */
    private int $rating = 1000;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $countryCode;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="winner")
     * @var Collection<FootballMatch> $victories
     */
    private Collection $victories;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="loser")
     * @var Collection<FootballMatch> $defeats
     */
    private Collection $defeats;

    /**
     * @var Collection<FootballMatch> $matchesHosting
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="hostingTeam")
     */
    private Collection $matchesHosting;

    /**
     * @var Collection<FootballMatch> $matchesHosting
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="receivingTeam")
     */
    private Collection $matchesReceiving;

    /**
     * @ORM\OneToMany(targetEntity=Trophy::class, mappedBy="team", orphanRemoval=true)
     */
    private Collection $trophies;

    /**
     * @ORM\OneToMany(targetEntity=Player::class, mappedBy="team", orphanRemoval=true)
     */
    private Collection $players;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="winner")
     */
    private Collection $winnedTournaments;

    /**
     * @ORM\ManyToMany(targetEntity=Tournament::class, mappedBy="finalPhasesTeams")
     */
    private Collection $finalPhasesTournaments;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="penaltiesWinner")
     */
    private Collection $matchesWonAtPenalties;

    /**
     * @ORM\ManyToMany(targetEntity=Tournament::class, mappedBy="finalists")
     */
    private Collection $finalistTournaments;

    #[Pure] public function __construct()
    {
        $this->victories = new ArrayCollection();
        $this->defeats = new ArrayCollection();
        $this->matchesHosting = new ArrayCollection();
        $this->matchesReceiving = new ArrayCollection();
        $this->trophies = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->winnedTournaments = new ArrayCollection();
        $this->finalistTournaments = new ArrayCollection();
        $this->finalPhasesTournaments = new ArrayCollection();
        $this->matchesWonAtPenalties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function addRating(int $rating): self
    {
        $this->rating += $rating;
        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getVictories(): Collection
    {
        return $this->victories;
    }

    public function addVictory(FootballMatch $victory): self
    {
        if (!$this->victories->contains($victory)) {
            $this->victories[] = $victory;
            $victory->setWinner($this);
        }

        return $this;
    }

    public function removeVictory(FootballMatch $victory): self
    {
        if ($this->victories->removeElement($victory)) {
            // set the owning side to null (unless already changed)
            if ($victory->getWinner() === $this) {
                $victory->setWinner(null);
            }
        }

        return $this;
    }

    public function getDefeats(): Collection
    {
        return $this->defeats;
    }

    public function addDefeat(FootballMatch $defeat): self
    {
        if (!$this->defeats->contains($defeat)) {
            $this->defeats[] = $defeat;
            $defeat->setLoser($this);
        }

        return $this;
    }

    public function removeDefeat(FootballMatch $defeat): self
    {
        if ($this->defeats->removeElement($defeat)) {
            // set the owning side to null (unless already changed)
            if ($defeat->getLoser() === $this) {
                $defeat->setLoser(null);
            }
        }

        return $this;
    }

    public function getDraws(): Collection
    {
        $draws = new ArrayCollection();
        foreach ($this->getMatchesHosting() as $match) {
            if ($match->getWinner() === null) {
                $draws->add($match);
            }
        }
        foreach ($this->getMatchesReceiving() as $match) {
            if ($match->getWinner() === null) {
                $draws->add($match);
            }
        }

        return $draws;
    }

    public function getGoalDiff(): int
    {
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($this->getVictories() as $victory) {
            $goalsFor += max($victory->getHostingTeamScore(), $victory->getReceivingTeamScore());
            $goalsAgainst += min($victory->getHostingTeamScore(), $victory->getReceivingTeamScore());
        }
        foreach ($this->getDefeats() as $defeat) {
            $goalsFor += min($defeat->getHostingTeamScore(), $defeat->getReceivingTeamScore());
            $goalsAgainst += max($defeat->getHostingTeamScore(), $defeat->getReceivingTeamScore());
        }
        foreach ($this->getDraws() as $draw) {
            $goalsFor += $draw->getHostingTeamScore();
            $goalsAgainst += $draw->getHostingTeamScore();
        }

        return $goalsFor - $goalsAgainst;
    }

    /**
     * @return Collection<FootballMatch>
     */
    public function getMatchesHosting(): Collection
    {
        return $this->matchesHosting;
    }

    /**
     * @return Collection<FootballMatch>
     */
    public function getMatchesReceiving(): Collection
    {
        return $this->matchesReceiving;
    }

    public function getLastMatches(int $count = 5): Collection
    {
        $matches = new ArrayCollection(
            array_merge($this->getMatchesHosting()->toArray(), $this->getMatchesReceiving()->toArray())
        );

        $matches = $matches->toArray();
        usort($matches, static function(FootballMatch $a, FootballMatch $b) {
            return $a->getId() < $b->getId();
        });

        /** @var array $matches */
        $matches = array_slice($matches, 0, 5);
        $matches = array_reverse($matches);

        return new ArrayCollection($matches);
    }

    #[Pure] public function getScoredGoalsPerMatch(): float
    {
        $scored = 0;
        foreach ($this->matchesReceiving as $match) {
            $scored += $match->getReceivingTeamScore();
        }
        foreach ($this->matchesHosting as $match) {
            $scored += $match->getHostingTeamScore();
        }

        $count = count($this->matchesHosting) + count($this->matchesReceiving);

        if ($count === 0) {
            return 0.;
        }

        return ($scored / $count) / 2;
    }

    #[Pure] public function getTakenGoalsPerMatch(): float
    {
        $taken = 0;
        foreach ($this->matchesReceiving as $match) {
            $taken += $match->getHostingTeamScore();
        }
        foreach ($this->matchesHosting as $match) {
            $taken += $match->getReceivingTeamScore();
        }

        $count = count($this->matchesHosting) + count($this->matchesReceiving);

        if ($count === 0) {
            return 0.;
        }

        return ($taken / $count) / 2;
    }

    /**
     * @return Collection<Trophy>
     */
    public function getTrophies(): Collection
    {
        return $this->trophies;
    }

    public function addTrophy(Trophy $trophy): self
    {
        if (!$this->trophies->contains($trophy)) {
            $this->trophies[] = $trophy;
            $trophy->setTeam($this);
        }

        return $this;
    }

    public function removeTrophy(Trophy $trophy): self
    {
        if ($this->trophies->removeElement($trophy)) {
            // set the owning side to null (unless already changed)
            if ($trophy->getTeam() === $this) {
                $trophy->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Trophy>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setTeam($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Tournament>
     */
    public function getWinnedTournaments(): Collection
    {
        return $this->winnedTournaments;
    }

    public function addWinnedTournament(Tournament $winnedTournament): self
    {
        if (!$this->winnedTournaments->contains($winnedTournament)) {
            $this->winnedTournaments[] = $winnedTournament;
            $winnedTournament->setWinner($this);
        }

        return $this;
    }

    public function removeWinnedTournament(Tournament $winnedTournament): self
    {
        if ($this->winnedTournaments->removeElement($winnedTournament)) {
            // set the owning side to null (unless already changed)
            if ($winnedTournament->getWinner() === $this) {
                $winnedTournament->setWinner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Tournament>
     */
    public function getFinalPhasesTournaments(): Collection
    {
        return $this->finalPhasesTournaments;
    }

    public function addFinalPhasesTournament(Tournament $finalPhasesTournament): self
    {
        if (!$this->finalPhasesTournaments->contains($finalPhasesTournament)) {
            $this->finalPhasesTournaments[] = $finalPhasesTournament;
            $finalPhasesTournament->addFinalPhasesTeam($this);
        }

        return $this;
    }

    public function removeFinalPhasesTournament(Tournament $finalPhasesTournament): self
    {
        if ($this->finalPhasesTournaments->removeElement($finalPhasesTournament)) {
            $finalPhasesTournament->removeFinalPhasesTeam($this);
        }

        return $this;
    }

    /**
     * @return Collection<FootballMatch>
     */
    public function getMatchesWonAtPenalties(): Collection
    {
        return $this->matchesWonAtPenalties;
    }

    public function addMatchesWonAtPenalty(FootballMatch $matchesWonAtPenalty): self
    {
        if (!$this->matchesWonAtPenalties->contains($matchesWonAtPenalty)) {
            $this->matchesWonAtPenalties[] = $matchesWonAtPenalty;
            $matchesWonAtPenalty->setPenaltiesWinner($this);
        }

        return $this;
    }

    public function removeMatchesWonAtPenalty(FootballMatch $matchesWonAtPenalty): self
    {
        if ($this->matchesWonAtPenalties->removeElement($matchesWonAtPenalty)) {
            // set the owning side to null (unless already changed)
            if ($matchesWonAtPenalty->getPenaltiesWinner() === $this) {
                $matchesWonAtPenalty->setPenaltiesWinner(null);
            }
        }

        return $this;
    }

    public function hasPalmares(): bool
    {
        return !(
            $this->winnedTournaments->isEmpty() &&
            $this->finalistTournaments->isEmpty() &&
            $this->finalPhasesTournaments->isEmpty()
        );
    }

    /**
     * @return Collection<Tournament>
     */
    public function getFinalistTournaments(): Collection
    {
        return $this->finalistTournaments;
    }

    public function addFinalistTournament(Tournament $finalistTournament): self
    {
        if (!$this->finalistTournaments->contains($finalistTournament)) {
            $this->finalistTournaments[] = $finalistTournament;
            $finalistTournament->addFinalist($this);
        }

        return $this;
    }

    public function removeFinalistTournament(Tournament $finalistTournament): self
    {
        if ($this->finalistTournaments->removeElement($finalistTournament)) {
            $finalistTournament->removeFinalist($this);
        }

        return $this;
    }
}
