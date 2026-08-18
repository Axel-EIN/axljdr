<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParticipationRepository::class)
 */
class Participation
{
    public const XP_BONUS_MULTIPLIER = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Scene::class, inversedBy="participations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $scene;

    /**
     * @ORM\Column(type="integer")
     */
    private $xpGagne;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estMort;

    /**
     * @ORM\ManyToOne(targetEntity=Personnage::class, inversedBy="participations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $personnage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estPj;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $xpBonus = false;

    /**
     * @ORM\OneToMany(targetEntity=Development::class, mappedBy="participation", orphanRemoval=true)
     */
    private $developments;

    public function __construct()
    {
        $this->developments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScene(): ?Scene
    {
        return $this->scene;
    }

    public function setScene(?Scene $scene): self
    {
        $this->scene = $scene;

        return $this;
    }

    public function getXpGagne(): ?int
    {
        return $this->xpGagne;
    }

    public function setXpGagne(int $xpGagne): self
    {
        $this->xpGagne = $xpGagne;

        return $this;
    }

    public function getEstMort(): ?bool
    {
        return $this->estMort;
    }

    public function setEstMort(bool $estMort): self
    {
        $this->estMort = $estMort;

        return $this;
    }

    public function getPersonnage(): ?Personnage
    {
        return $this->personnage;
    }

    public function setPersonnage(?Personnage $personnage): self
    {
        $this->personnage = $personnage;

        return $this;
    }

    public function getEstPj(): ?bool
    {
        return $this->estPj;
    }

    public function setEstPj(bool $estPj): self
    {
        $this->estPj = $estPj;

        return $this;
    }

    public function getXpBonus(): bool
    {
        return (bool) $this->xpBonus;
    }

    public function setXpBonus(bool $xpBonus): self
    {
        $this->xpBonus = $xpBonus;

        return $this;
    }

    /**
     * @return Collection|Development[]
     */
    public function getDevelopments(): Collection
    {
        return $this->developments;
    }

    public function getXpEffectif(): int
    {
        return (int) $this->xpGagne * ($this->getXpBonus() ? self::XP_BONUS_MULTIPLIER : 1);
    }
}
