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
     * @ORM\OneToMany(targetEntity=FootballMatch::class, mappedBy="hostingTeam", orphanRemoval=true)
     */
    private Collection $footballMatches;

    /**
     * @ORM\Column(type="integer")
     */
    private int $rating = 1200;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $countryCode;

    public function __construct()
    {
        $this->footballMatches = new ArrayCollection();
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
            $footballMatch->setHostingTeam($this);
        }

        return $this;
    }

    public function removeFootballMatch(FootballMatch $footballMatch): self
    {
        if ($this->footballMatches->removeElement($footballMatch)) {
            // set the owning side to null (unless already changed)
            if ($footballMatch->getHostingTeam() === $this) {
                $footballMatch->setHostingTeam(null);
            }
        }

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
}
