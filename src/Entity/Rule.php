<?php

namespace App\Entity;

use App\Repository\RuleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RuleRepository::class)
 */
class Rule
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
    private $base;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdf;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $part1titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part1aside;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $part2titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part2aside;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $part3titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part3aside;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $part4titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part4aside;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part5;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $part5titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $part5aside;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $liste;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $listeIntro;

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

    public function setBase(bool $base): self
    {
        $this->base = $base;

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

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    // content part 1

    public function getPart1(): ?string
    {
        return $this->part1;
    }

    public function setPart1(?string $part1): self
    {
        $this->part1 = $part1;

        return $this;
    }

    public function getPart1titre(): ?string
    {
        return $this->part1titre;
    }

    public function setPart1titre(?string $part1titre): self
    {
        $this->part1titre = $part1titre;

        return $this;
    }

    public function getPart1aside(): ?string
    {
        return $this->part1aside;
    }

    public function setPart1aside(?string $part1aside): self
    {
        $this->part1aside = $part1aside;

        return $this;
    }

    // content part 2

    public function getPart2titre(): ?string
    {
        return $this->part2titre;
    }

    public function setPart2titre(?string $part2titre): self
    {
        $this->part2titre = $part2titre;

        return $this;
    }

    public function getPart2(): ?string
    {
        return $this->part2;
    }

    public function setPart2(?string $part2): self
    {
        $this->part2 = $part2;

        return $this;
    }

    public function getPart2aside(): ?string
    {
        return $this->part2aside;
    }

    public function setPart2aside(?string $part2aside): self
    {
        $this->part2aside = $part2aside;

        return $this;
    }

    // content part 3

    public function getPart3titre(): ?string
    {
        return $this->part3titre;
    }

    public function setPart3titre(?string $part3titre): self
    {
        $this->part3titre = $part3titre;

        return $this;
    }

    public function getPart3(): ?string
    {
        return $this->part3;
    }

    public function setPart3(?string $part3): self
    {
        $this->part3 = $part3;

        return $this;
    }

    public function getPart3aside(): ?string
    {
        return $this->part3aside;
    }

    public function setPart3aside(?string $part3aside): self
    {
        $this->part3aside = $part3aside;

        return $this;
    }

    public function getPart4(): ?string
    {
        return $this->part4;
    }

    public function setPart4(?string $part4): self
    {
        $this->part4 = $part4;

        return $this;
    }

    public function getPart4titre(): ?string
    {
        return $this->part4titre;
    }

    public function setPart4titre(?string $part4titre): self
    {
        $this->part4titre = $part4titre;

        return $this;
    }

    public function getPart4aside(): ?string
    {
        return $this->part4aside;
    }

    public function setPart4aside(?string $part4aside): self
    {
        $this->part4aside = $part4aside;

        return $this;
    }

    public function getPart5(): ?string
    {
        return $this->part5;
    }

    public function setPart5(?string $part5): self
    {
        $this->part5 = $part5;

        return $this;
    }

    public function getPart5titre(): ?string
    {
        return $this->part5titre;
    }

    public function setPart5titre(?string $part5titre): self
    {
        $this->part5titre = $part5titre;

        return $this;
    }

    public function getPart5aside(): ?string
    {
        return $this->part5aside;
    }

    public function setPart5aside(?string $part5aside): self
    {
        $this->part5aside = $part5aside;

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

    public function getListe(): ?string
    {
        return $this->liste;
    }

    public function setListe(?string $liste): self
    {
        $this->liste = $liste;

        return $this;
    }

    public function getListeIntro(): ?string
    {
        return $this->listeIntro;
    }

    public function setListeIntro(?string $listeIntro): self
    {
        $this->listeIntro = $listeIntro;

        return $this;
    }
}