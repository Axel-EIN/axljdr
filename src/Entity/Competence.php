<?php

namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompetenceRepository::class)
 */
class Competence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $trait;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $specialisation1;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $specialisation2;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $specialisation3;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $capacite;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $degradante;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $globale;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $motCle1;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $motCle2;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $specialisation4;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $specialisation5;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $specialisation6;

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

    public function getTrait(): ?string
    {
        return $this->trait;
    }

    public function setTrait(string $trait): self
    {
        $this->trait = $trait;

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

    public function getSpecialisation1(): ?string
    {
        return $this->specialisation1;
    }

    public function setSpecialisation1(?string $specialisation1): self
    {
        $this->specialisation1 = $specialisation1;

        return $this;
    }

    public function getSpecialisation2(): ?string
    {
        return $this->specialisation2;
    }

    public function setSpecialisation2(?string $specialisation2): self
    {
        $this->specialisation2 = $specialisation2;

        return $this;
    }

    public function getSpecialisation3(): ?string
    {
        return $this->specialisation3;
    }

    public function setSpecialisation3(?string $specialisation3): self
    {
        $this->specialisation3 = $specialisation3;

        return $this;
    }

    public function getCapacite(): ?string
    {
        return $this->capacite;
    }

    public function setCapacite(?string $capacite): self
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDegradante(): ?bool
    {
        return $this->degradante;
    }

    public function setDegradante(?bool $degradante): self
    {
        $this->degradante = $degradante;

        return $this;
    }

    public function getGlobale(): ?bool
    {
        return $this->globale;
    }

    public function setGlobale(?bool $globale): self
    {
        $this->globale = $globale;

        return $this;
    }

    public function getMotCle1(): ?string
    {
        return $this->motCle1;
    }

    public function setMotCle1(?string $motCle1): self
    {
        $this->motCle1 = $motCle1;

        return $this;
    }

    public function getMotCle2(): ?string
    {
        return $this->motCle2;
    }

    public function setMotCle2(?string $motCle2): self
    {
        $this->motCle2 = $motCle2;

        return $this;
    }

    public function getSpecialisation4(): ?string
    {
        return $this->specialisation4;
    }

    public function setSpecialisation4(?string $specialisation4): self
    {
        $this->specialisation4 = $specialisation4;

        return $this;
    }

    public function getSpecialisation5(): ?string
    {
        return $this->specialisation5;
    }

    public function setSpecialisation5(?string $specialisation5): self
    {
        $this->specialisation5 = $specialisation5;

        return $this;
    }

    public function getSpecialisation6(): ?string
    {
        return $this->specialisation6;
    }

    public function setSpecialisation6(?string $specialisation6): self
    {
        $this->specialisation6 = $specialisation6;

        return $this;
    }
}