<?php

namespace App\Entity;

use App\Repository\AwardRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=AwardRepository::class)
 */
class Award
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
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="awards")
     */
    private Tournament $tournament;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="awards")
     * @ORM\JoinColumn(nullable=false)
     */
    private Player $player;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private \DateTimeInterface $givenAt;

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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getGivenAt(): ?\DateTimeInterface
    {
        return $this->givenAt;
    }

    public function setGivenAt(\DateTimeInterface $givenAt): self
    {
        $this->givenAt = $givenAt;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
