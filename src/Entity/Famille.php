<?php

namespace App\Entity;

use App\Repository\FamilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FamilleRepository::class)
 */
class Famille
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity=Clan::class, inversedBy="familles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $clan;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mon;

    /**
     * @ORM\OneToOne(targetEntity=Personnage::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $chef;

    /**
     * @ORM\OneToMany(targetEntity=Personnage::class, mappedBy="famille")
     */
    private $personnages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bonusStatNom;

    public function __construct()
    {
        $this->personnages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getClan(): ?Clan
    {
        return $this->clan;
    }

    public function setClan(?Clan $clan): self
    {
        $this->clan = $clan;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMon(): ?string
    {
        return $this->mon;
    }

    public function setMon(?string $mon): self
    {
        $this->mon = $mon;

        return $this;
    }

    public function getChef(): ?Personnage
    {
        return $this->chef;
    }

    public function setChef(Personnage $chef): self
    {
        $this->chef = $chef;

        return $this;
    }

    public function getBonusStatNom(): ?string
    {
        return $this->bonusStatNom;
    }

    public function setBonusStatNom(?string $bonusStatNom): self
    {
        $this->bonusStatNom = $bonusStatNom;

        return $this;
    }

    public function getPersonnages(): Collection
    {
        return $this->personnages;
    }
}
