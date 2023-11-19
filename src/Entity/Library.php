<?php

namespace App\Entity;

use App\Repository\LibraryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LibraryRepository::class)
 */
class Library
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $base;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $entity;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tabField;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subTabField;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filterField;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdf;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $aside;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mixable;

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

    public function getBase(): ?bool
    {
        return $this->base;
    }

    public function setBase(?bool $base): self
    {
        $this->base = $base;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

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

    public function getTabField(): ?string
    {
        return $this->tabField;
    }

    public function setTabField(?string $tabField): self
    {
        $this->tabField = $tabField;

        return $this;
    }

    public function getSubTabField(): ?string
    {
        return $this->subTabField;
    }

    public function setSubTabField(?string $subTabField): self
    {
        $this->subTabField = $subTabField;

        return $this;
    }

    public function getFilterField(): ?string
    {
        return $this->filterField;
    }

    public function setFilterField(?string $filterField): self
    {
        $this->filterField = $filterField;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    public function getAside(): ?string
    {
        return $this->aside;
    }

    public function setAside(?string $aside): self
    {
        $this->aside = $aside;

        return $this;
    }

    public function getMixable(): ?bool
    {
        return $this->mixable;
    }

    public function setMixable(?bool $mixable): self
    {
        $this->mixable = $mixable;

        return $this;
    }
}
