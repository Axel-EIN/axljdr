<?php

namespace App\Entity;

use App\Repository\ClanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClanRepository::class)
 */
class Clan
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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $estMajeur;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $couleur;

    /**
     * @ORM\OneToMany(targetEntity=Ecole::class, mappedBy="clan")
     */
    private $ecoles;

    /**
     * @ORM\OneToMany(targetEntity=Personnage::class, mappedBy="clan")
     */
    private $personnages;

    /**
     * @ORM\OneToOne(targetEntity=Personnage::class, cascade={"persist", "remove"})
     */
    private $chef;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mon;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Lieu::class, mappedBy="clan")
     */
    private $lieux;

    /**
     * @ORM\OneToMany(targetEntity=Famille::class, mappedBy="clan")
     */
    private $familles;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $longDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $citation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $territoireCarte;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $territoireDesc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $video;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $genre;

    /**
     * @ORM\OneToMany(targetEntity=Avantage::class, mappedBy="discountClan")
     */
    private $avantages;

    /**
     * @ORM\OneToMany(targetEntity=Avantage::class, mappedBy="discountClan2")
     */
    private $avantages2;

    public function __construct()
    {
        $this->ecoles = new ArrayCollection();
        $this->personnages = new ArrayCollection();
        $this->lieux = new ArrayCollection();
        $this->familles = new ArrayCollection();
        $this->avantages = new ArrayCollection();
        $this->avantages2 = new ArrayCollection();
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

    public function getEstMajeur(): ?int
    {
        return $this->estMajeur;
    }

    public function setEstMajeur(?int $estMajeur): self
    {
        $this->estMajeur = $estMajeur;

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
            $ecole->setClan($this);
        }

        return $this;
    }

    public function removeEcole(Ecole $ecole): self
    {
        if ($this->ecoles->removeElement($ecole)) {
            // set the owning side to null (unless already changed)
            if ($ecole->getClan() === $this) {
                $ecole->setClan(null);
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
            $personnage->setClan($this);
        }

        return $this;
    }

    public function removePersonnage(Personnage $personnage): self
    {
        if ($this->personnages->removeElement($personnage)) {
            // set the owning side to null (unless already changed)
            if ($personnage->getClan() === $this) {
                $personnage->setClan(null);
            }
        }

        return $this;
    }

    public function getChef(): ?Personnage
    {
        return $this->chef;
    }

    public function setChef(?Personnage $chef): self
    {
        $this->chef = $chef;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Lieu[]
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(Lieu $lieux): self
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux[] = $lieux;
            $lieux->setClan($this);
        }

        return $this;
    }

    public function removeLieux(Lieu $lieux): self
    {
        if ($this->lieux->removeElement($lieux)) {
            // set the owning side to null (unless already changed)
            if ($lieux->getClan() === $this) {
                $lieux->setClan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Famille>
     */
    public function getFamilles(): Collection
    {
        return $this->familles;
    }

    public function addFamille(Famille $famille): self
    {
        if (!$this->familles->contains($famille)) {
            $this->familles[] = $famille;
            $famille->setClan($this);
        }

        return $this;
    }

    public function removeFamille(Famille $famille): self
    {
        if ($this->familles->removeElement($famille)) {
            // set the owning side to null (unless already changed)
            if ($famille->getClan() === $this) {
                $famille->setClan(null);
            }
        }

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): self
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getCitation(): ?string
    {
        return $this->citation;
    }

    public function setCitation(?string $citation): self
    {
        $this->citation = $citation;

        return $this;
    }

    public function getTerritoireCarte(): ?string
    {
        return $this->territoireCarte;
    }

    public function setTerritoireCarte(?string $territoireCarte): self
    {
        $this->territoireCarte = $territoireCarte;

        return $this;
    }

    public function getTerritoireDesc(): ?string
    {
        return $this->territoireDesc;
    }

    public function setTerritoireDesc(?string $territoireDesc): self
    {
        $this->territoireDesc = $territoireDesc;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;

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
            $avantage->setDiscountClan($this);
        }

        return $this;
    }

    public function removeAvantage(Avantage $avantage): self
    {
        if ($this->avantages->removeElement($avantage)) {
            // set the owning side to null (unless already changed)
            if ($avantage->getDiscountClan() === $this) {
                $avantage->setDiscountClan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avantage>
     */
    public function getAvantages2(): Collection
    {
        return $this->avantages2;
    }

    public function addAvantages2(Avantage $avantages2): self
    {
        if (!$this->avantages2->contains($avantages2)) {
            $this->avantages2[] = $avantages2;
            $avantages2->setDiscountClan2($this);
        }

        return $this;
    }

    public function removeAvantages2(Avantage $avantages2): self
    {
        if ($this->avantages2->removeElement($avantages2)) {
            // set the owning side to null (unless already changed)
            if ($avantages2->getDiscountClan2() === $this) {
                $avantages2->setDiscountClan2(null);
            }
        }

        return $this;
    }
}
