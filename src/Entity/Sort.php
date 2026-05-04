<?php

namespace App\Entity;

use App\Repository\SortRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SortRepository::class)
 */
class Sort
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
     * @ORM\Column(type="string", length=60)
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $anneau;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $niveau;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $portee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $duree;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $augmentations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $keyword1;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $keyword2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keyword3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalName;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $kihoType;

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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getAnneau(): ?string
    {
        return $this->anneau;
    }

    public function setAnneau(?string $anneau): self
    {
        $this->anneau = $anneau;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getPortee(): ?string
    {
        return $this->portee;
    }

    public function setPortee(?string $portee): self
    {
        $this->portee = $portee;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getAugmentations(): ?string
    {
        return $this->augmentations;
    }

    public function setAugmentations(string $augmentations): self
    {
        $this->augmentations = $augmentations;

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

    public function getKeyword1(): ?string
    {
        return $this->keyword1;
    }

    public function setKeyword1(?string $keyword1): self
    {
        $this->keyword1 = $keyword1;

        return $this;
    }

    public function getKeyword2(): ?string
    {
        return $this->keyword2;
    }

    public function setKeyword2(?string $keyword2): self
    {
        $this->keyword2 = $keyword2;

        return $this;
    }

    public function getKeyword3(): ?string
    {
        return $this->keyword3;
    }

    public function setKeyword3(?string $keyword3): self
    {
        $this->keyword3 = $keyword3;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getKihoType(): ?string
    {
        return $this->kihoType;
    }

    public function setKihoType(?string $kihoType): self
    {
        $this->kihoType = $kihoType;

        return $this;
    }
}
