<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    private ?DateTime $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $endedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="tournament", orphanRemoval=true)
     */
    private Collection $footballMatches;

    public function __construct()
    {
        $this->footballMatches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): self
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
     * @return Collection|FootballMatch[]
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
}
