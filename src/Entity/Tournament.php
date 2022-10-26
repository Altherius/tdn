<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
 */
class Tournament
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $endedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $major = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="tournament", orphanRemoval=true)
     */
    private Collection $footballMatches;

    /**
     * @ORM\OneToMany(targetEntity=Trophy::class, mappedBy="tournament", orphanRemoval=true)
     */
    private Collection $trophies;

    /**
     * @ORM\OneToMany(targetEntity=Award::class, mappedBy="tournament")
     */
    private Collection $awards;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="winnedTournaments")
     */
    private ?Team $winner = null;

    /**
     * @ORM\ManyToMany(targetEntity=Team::class, inversedBy="finalPhasesTournaments")
     * @ORM\JoinTable(name="tournament_final_phases_teams")
     */
    private Collection $finalPhasesTeams;

    /**
     * @ORM\ManyToMany(targetEntity=Team::class, inversedBy="finalistTournaments")
     * @ORM\JoinTable(name="tournament_finalists_teams")
     */
    private Collection $finalists;

    /**
     * @ORM\Column(type="float")
     */
    #[PositiveOrZero]
    private float $eloMultiplier = 1.;

    #[Pure] public function __construct()
    {
        $this->footballMatches = new ArrayCollection();
        $this->trophies = new ArrayCollection();
        $this->awards = new ArrayCollection();
        $this->finalPhasesTeams = new ArrayCollection();
        $this->finalists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<FootballMatch>
     */
    public function getFootballMatches(): Collection
    {
        return $this->footballMatches;
    }

    public function addFootballMatch(FootballMatch $footballMatch): self
    {
        if (!$this->footballMatches->contains($footballMatch)) {
            $this->footballMatches[] = $footballMatch;
            $footballMatch->setTournament($this);
        }

        return $this;
    }

    public function removeFootballMatch(FootballMatch $footballMatch): self
    {
        if ($this->footballMatches->removeElement($footballMatch)) {
            // set the owning side to null (unless already changed)
            if ($footballMatch->getTournament() === $this) {
                $footballMatch->setTournament(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
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
            $trophy->setTournament($this);
        }

        return $this;
    }

    public function removeTrophy(Trophy $trophy): self
    {
        if ($this->trophies->removeElement($trophy)) {
            // set the owning side to null (unless already changed)
            if ($trophy->getTournament() === $this) {
                $trophy->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Award>
     */
    public function getAwards(): Collection
    {
        return $this->awards;
    }

    public function addAward(Award $award): self
    {
        if (!$this->awards->contains($award)) {
            $this->awards[] = $award;
            $award->setTournament($this);
        }

        return $this;
    }

    public function removeAward(Award $award): self
    {
        if ($this->awards->removeElement($award)) {
            // set the owning side to null (unless already changed)
            if ($award->getTournament() === $this) {
                $award->setTournament(null);
            }
        }

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

    /**
     * @return Collection<Team>
     */
    public function getFinalPhasesTeams(): Collection
    {
        return $this->finalPhasesTeams;
    }

    public function addFinalPhasesTeam(Team $finalPhasesTeam): self
    {
        if (!$this->finalPhasesTeams->contains($finalPhasesTeam)) {
            $this->finalPhasesTeams[] = $finalPhasesTeam;
        }

        return $this;
    }

    public function removeFinalPhasesTeam(Team $finalPhasesTeam): self
    {
        $this->finalPhasesTeams->removeElement($finalPhasesTeam);

        return $this;
    }

    /**
     * @return Collection<Team>
     */
    public function getFinalists(): Collection
    {
        return $this->finalists;
    }

    public function addFinalist(Team $finalist): self
    {
        if (!$this->finalists->contains($finalist)) {
            $this->finalists[] = $finalist;
        }

        return $this;
    }

    public function removeFinalist(Team $finalist): self
    {
        $this->finalists->removeElement($finalist);

        return $this;
    }

    public function getEloMultiplier(): ?float
    {
        return $this->eloMultiplier;
    }

    public function setEloMultiplier(float $eloMultiplier): self
    {
        $this->eloMultiplier = $eloMultiplier;

        return $this;
    }
}
