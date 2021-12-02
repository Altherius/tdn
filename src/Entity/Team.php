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
    private int $rating = 1200;

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
    private $trophies;

    /**
     * @ORM\OneToMany(targetEntity=Player::class, mappedBy="team", orphanRemoval=true)
     */
    private $players;

    #[Pure] public function __construct()
    {
        $this->victories = new ArrayCollection();
        $this->defeats = new ArrayCollection();
        $this->matchesHosting = new ArrayCollection();
        $this->matchesReceiving = new ArrayCollection();
        $this->trophies = new ArrayCollection();
        $this->players = new ArrayCollection();
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


    public function getMatchesHosting(): Collection
    {
        return $this->matchesHosting;
    }

    public function getMatchesReceiving(): Collection
    {
        return $this->matchesReceiving;
    }

    public function getScoredGoalsPerMatch(): float
    {
        $scored = 0;
        foreach ($this->matchesReceiving as $match) {
            $scored += $match->getReceivingTeamScore();
        }
        foreach ($this->matchesHosting as $match) {
            $scored += $match->getHostingTeamScore();
        }

        $count = count($this->matchesHosting) + count($this->matchesReceiving);

        return ($scored / $count) / 2;
    }

    public function getTakenGoalsPerMatch(): float
    {
        $taken = 0;
        foreach ($this->matchesReceiving as $match) {
            $taken += $match->getHostingTeamScore();
        }
        foreach ($this->matchesHosting as $match) {
            $taken += $match->getReceivingTeamScore();
        }

        $count = count($this->matchesHosting) + count($this->matchesReceiving);

        return ($taken / $count) / 2;
    }

    /**
     * @return Collection|Trophy[]
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
     * @return Collection|Player[]
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
}
