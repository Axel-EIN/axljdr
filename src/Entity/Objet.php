<?php

namespace App\Entity;

use App\Repository\ObjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ObjetRepository::class)
 */
class Objet
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
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $prix;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numero;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ndArmure;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reduction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $taille;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $vd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $forceArc;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $regles;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motCle1;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $poids;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $motCle2;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self
    {
        $this->prix = $prix;

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

    public function getReduction(): ?int
    {
        return $this->reduction;
    }

    public function setReduction(?int $reduction): self
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getVd(): ?string
    {
        return $this->vd;
    }

    public function setVd(?string $vd): self
    {
        $this->vd = $vd;

        return $this;
    }

    public function getForceArc(): ?int
    {
        return $this->forceArc;
    }

    public function setForceArc(?int $forceArc): self
    {
        $this->forceArc = $forceArc;

        return $this;
    }

    public function getRegles(): ?string
    {
        return $this->regles;
    }

    public function setRegles(?string $regles): self
    {
        $this->regles = $regles;

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

    public function getNdArmure(): ?int
    {
        return $this->ndArmure;
    }

    public function setNdArmure(?int $ndArmure): self
    {
        $this->ndArmure = $ndArmure;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(?string $taille): self
    {
        $this->taille = $taille;

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

    public function getPoids(): ?string
    {
        return $this->poids;
    }

    public function setPoids(?string $poids): self
    {
        $this->poids = $poids;

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
}
