<?php

namespace App\Entity;

use App\Repository\PersonnageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonnageRepository::class)
 */
class Personnage
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
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $illustration;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estPj;

    /**
     * @ORM\ManyToOne(targetEntity=Clan::class, inversedBy="personnages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $clan;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="personnages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $classe;

    /**
     * @ORM\ManyToOne(targetEntity=Ecole::class, inversedBy="personnages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $ecole;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="personnages")
     */
    private $joueur;

    /**
     * @ORM\OneToMany(targetEntity=Archive::class, mappedBy="auteur")
     */
    private $archives;

    /**
     * @ORM\OneToOne(targetEntity=FichePersonnage::class, mappedBy="personnage", cascade={"persist", "remove"})
     */
    private $fichePersonnage;

    /**
     * @ORM\OneToMany(targetEntity=Participation::class, mappedBy="personnage", orphanRemoval=true)
     */
    private $participations;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estMort;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked;

    public function __construct()
    {
        $this->archives = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTitres(): ?string
    {
        return $this->titres;
    }

    public function setTitres(?string $titres): self
    {
        $this->titres = $titres;

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

    public function getIllustration(): ?string
    {
        return $this->illustration;
    }

    public function setIllustration(string $illustration): self
    {
        $this->illustration = $illustration;

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

    public function getEstPj(): ?bool
    {
        return $this->estPj;
    }

    public function setEstPj(bool $estPj): self
    {
        $this->estPj = $estPj;

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

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getEcole(): ?Ecole
    {
        return $this->ecole;
    }

    public function setEcole(?Ecole $ecole): self
    {
        $this->ecole = $ecole;

        return $this;
    }

    public function getJoueur(): ?Utilisateur
    {
        return $this->joueur;
    }

    public function setJoueur(?Utilisateur $joueur): self
    {
        $this->joueur = $joueur;

        return $this;
    }

    /**
     * @return Collection|Archive[]
     */
    public function getArchives(): Collection
    {
        return $this->archives;
    }

    public function addArchive(Archive $archive): self
    {
        if (!$this->archives->contains($archive)) {
            $this->archives[] = $archive;
            $archive->setAuteur($this);
        }

        return $this;
    }

    public function removeArchive(Archive $archive): self
    {
        if ($this->archives->removeElement($archive)) {
            // set the owning side to null (unless already changed)
            if ($archive->getAuteur() === $this) {
                $archive->setAuteur(null);
            }
        }

        return $this;
    }

    public function getFichePersonnage(): ?FichePersonnage
    {
        return $this->fichePersonnage;
    }

    public function setFichePersonnage(FichePersonnage $fichePersonnage): self
    {
        // set the owning side of the relation if necessary
        if ($fichePersonnage->getPersonnage() !== $this) {
            $fichePersonnage->setPersonnage($this);
        }

        $this->fichePersonnage = $fichePersonnage;

        return $this;
    }

    /**
     * @return Collection|Participation[]
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $une_participation): self
    {
        if (!$this->participations->contains($une_participation)) {
            $this->participations[] = $une_participation;
            $une_participation->setPersonnage($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $une_participation): self
    {
        if ($this->participations->removeElement($une_participation)) {
            // set the owning side to null (unless already changed)
            if ($une_participation->getPersonnage() === $this) {
                $une_participation->setPersonnage(null);
            }
        }

        return $this;
    }

    public function getEstMort(): ?bool
    {
        return $this->estMort;
    }

    public function setEstMort(bool $estMort): self
    {
        $this->estMort = $estMort;

        return $this;
    }

    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
