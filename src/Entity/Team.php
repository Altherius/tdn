<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
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
     */
    private int $rating = 1200;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $countryCode;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="winner")
     */
    private $victories;

    /**
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="loser")
     */
    private $defeats;

    public function __construct()
    {
        $this->footballMatches = new ArrayCollection();
        $this->victories = new ArrayCollection();
        $this->defeats = new ArrayCollection();
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

    /**
     * @return Collection|FootballMatch[]
     */
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

    /**
     * @return Collection|FootballMatch[]
     */
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
}
