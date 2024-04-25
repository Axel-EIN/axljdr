<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClasseRepository::class)
 */
class Classe
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
     * @ORM\OneToMany(targetEntity=Ecole::class, mappedBy="classe")
     */
    private $ecoles;

    /**
     * @ORM\OneToMany(targetEntity=Personnage::class, mappedBy="classe")
     */
    private $personnages;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $icone;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $couleur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $citation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity=Avantage::class, mappedBy="discountClasse")
     */
    private $avantages;

    /**
     * @ORM\OneToMany(targetEntity=Avantage::class, mappedBy="exclusive")
     */
    private $exclusiveAvantages;

    public function __construct()
    {
        $this->ecoles = new ArrayCollection();
        $this->personnages = new ArrayCollection();
        $this->avantages = new ArrayCollection();
        $this->exclusiveAvantages = new ArrayCollection();
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

    /**
     * @return Collection|Ecole[]
     */
    public function getEcoles(): Collection
    {
        return $this->ecoles;
    }

    public function addEcole(Ecole $ecole): self
    {
        if (!$this->ecoles->contains($ecole)) {
            $this->ecoles[] = $ecole;
            $ecole->setClasse($this);
        }

        return $this;
    }

    public function removeEcole(Ecole $ecole): self
    {
        if ($this->ecoles->removeElement($ecole)) {
            // set the owning side to null (unless already changed)
            if ($ecole->getClasse() === $this) {
                $ecole->setClasse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Personnage[]
     */
    public function getPersonnages(): Collection
    {
        return $this->personnages;
    }

    public function addPersonnage(Personnage $personnage): self
    {
        if (!$this->personnages->contains($personnage)) {
            $this->personnages[] = $personnage;
            $personnage->setClasse($this);
        }

        return $this;
    }

    public function removePersonnage(Personnage $personnage): self
    {
        if ($this->personnages->removeElement($personnage)) {
            // set the owning side to null (unless already changed)
            if ($personnage->getClasse() === $this) {
                $personnage->setClasse(null);
            }
        }

        return $this;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(string $icone): self
    {
        $this->icone = $icone;

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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getCitation(): ?string
    {
        return $this->citation;
    }

    public function setCitation(string $citation): self
    {
        $this->citation = $citation;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Avantage>
     */
    public function getAvantages(): Collection
    {
        return $this->avantages;
    }

    public function addAvantage(Avantage $avantage): self
    {
        if (!$this->avantages->contains($avantage)) {
            $this->avantages[] = $avantage;
            $avantage->setDiscountClasse($this);
        }

        return $this;
    }

    public function removeAvantage(Avantage $avantage): self
    {
        if ($this->avantages->removeElement($avantage)) {
            // set the owning side to null (unless already changed)
            if ($avantage->getDiscountClasse() === $this) {
                $avantage->setDiscountClasse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avantage>
     */
    public function getExclusiveAvantages(): Collection
    {
        return $this->exclusiveAvantages;
    }

    public function addExclusiveAvantage(Avantage $exclusiveAvantage): self
    {
        if (!$this->exclusiveAvantages->contains($exclusiveAvantage)) {
            $this->exclusiveAvantages[] = $exclusiveAvantage;
            $exclusiveAvantage->setExclusive($this);
        }

        return $this;
    }

    public function removeExclusiveAvantage(Avantage $exclusiveAvantage): self
    {
        if ($this->exclusiveAvantages->removeElement($exclusiveAvantage)) {
            // set the owning side to null (unless already changed)
            if ($exclusiveAvantage->getExclusive() === $this) {
                $exclusiveAvantage->setExclusive(null);
            }
        }

        return $this;
    }
}
