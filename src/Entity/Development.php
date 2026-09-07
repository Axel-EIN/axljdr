<?php

namespace App\Entity;

use App\Repository\DevelopmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DevelopmentRepository::class)
 */
class Development
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Participation::class, inversedBy="developments")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $participation;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipation(): ?Participation
    {
        return $this->participation;
    }

    public function setParticipation(?Participation $participation): self
    {
        $this->participation = $participation;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPersonnage(): ?Personnage
    {
        return $this->participation?->getPersonnage();
    }

    public function getScene(): ?Scene
    {
        return $this->participation?->getScene();
    }

    public function getEstMort(): bool
    {
        return (bool) $this->participation?->getEstMort();
    }
}
