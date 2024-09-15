<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LieuRepository::class)
 */
class Lieu
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $carte;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Clan::class, inversedBy="lieux")
     */
    private $clan;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $locX;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Assert\Range(min=1, max=99)
     */
    private $locY;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCarte(): ?string
    {
        return $this->carte;
    }

    public function setCarte(?string $carte): self
    {
        $this->carte = $carte;

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

    public function getClan(): ?Clan
    {
        return $this->clan;
    }

    public function setClan(?Clan $clan): self
    {
        $this->clan = $clan;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getLocX(): ?string
    {
        return $this->locX;
    }

    public function setLocX(?string $locX): self
    {
        $this->locX = $locX;

        return $this;
    }

    public function getLocY(): ?string
    {
        return $this->locY;
    }

    public function setLocY(?string $locY): self
    {
        $this->locY = $locY;

        return $this;
    }
}
