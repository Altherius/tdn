<?php

namespace App\Entity;

use App\Repository\TrophyRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=TrophyRepository::class)
 */
class Trophy
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
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="trophies")
     * @ORM\JoinColumn(nullable=false)
     */
    private Tournament $tournament;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private DateTimeInterface $givenAt;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="trophies")
     * @ORM\JoinColumn(nullable=false)
     */
    private Team $team;

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

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getGivenAt(): ?DateTimeInterface
    {
        return $this->givenAt;
    }

    public function setGivenAt(DateTimeInterface $givenAt): self
    {
        $this->givenAt = $givenAt;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
