<?php

namespace App\Entity;

use App\Repository\AvantageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AvantageRepository::class)
 */
class Avantage
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
     * @ORM\Column(type="boolean")
     */
    private $desavantage;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $cout;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $discount;

    /**
     * @ORM\ManyToOne(targetEntity=Clan::class, inversedBy="avantages")
     */
    private $discountClan;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="avantages")
     */
    private $discountClasse;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="exclusiveAvantages")
     */
    private $exclusive;

    /**
     * @ORM\ManyToOne(targetEntity=Clan::class, inversedBy="avantages2")
     */
    private $discountClan2;

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

    public function getDesavantage(): ?bool
    {
        return $this->desavantage;
    }

    public function setDesavantage(bool $desavantage): self
    {
        $this->desavantage = $desavantage;

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

    public function getCout(): ?int
    {
        return $this->cout;
    }

    public function setCout(int $cout): self
    {
        $this->cout = $cout;

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

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(?int $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getDiscountClan(): ?Clan
    {
        return $this->discountClan;
    }

    public function setDiscountClan(?Clan $discountClan): self
    {
        $this->discountClan = $discountClan;

        return $this;
    }

    public function getDiscountClasse(): ?Classe
    {
        return $this->discountClasse;
    }

    public function setDiscountClasse(?Classe $discountClasse): self
    {
        $this->discountClasse = $discountClasse;

        return $this;
    }

    public function getExclusive(): ?Classe
    {
        return $this->exclusive;
    }

    public function setExclusive(?Classe $exclusive): self
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    public function getDiscountClan2(): ?Clan
    {
        return $this->discountClan2;
    }

    public function setDiscountClan2(?Clan $discountClan2): self
    {
        $this->discountClan2 = $discountClan2;

        return $this;
    }
}
