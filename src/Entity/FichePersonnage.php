<?php

namespace App\Entity;

use App\Repository\FichePersonnageRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Avantage;
use App\Entity\Competence;
use App\Entity\Objet;

/**
 * @ORM\Entity(repositoryClass=FichePersonnageRepository::class)
 */
class FichePersonnage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Personnage::class, inversedBy="fichePersonnage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $personnage;

    /**
     * @ORM\Column(type="integer")
     */
    private $creationExp;

    /**
     * @ORM\ManyToOne(targetEntity=Avantage::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $avantage1;

    /**
     * @ORM\ManyToOne(targetEntity=Avantage::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $avantage2;

    /**
     * @ORM\ManyToOne(targetEntity=Avantage::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $desavantage1;

    /**
     * @ORM\ManyToOne(targetEntity=Avantage::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $desavantage2;

    /**
     * @ORM\ManyToOne(targetEntity=Objet::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $arme;

    /**
     * @ORM\ManyToOne(targetEntity=Objet::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $armure;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reductionSpecial;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1, nullable=true)
     */
    private $honneur;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1, nullable=true)
     */
    private $gloire;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1, nullable=true)
     */
    private $infamie;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1, nullable=true)
     */
    private $souillure;

    /**
     * @ORM\Column(type="smallint")
     */
    private $constitution;

    /**
     * @ORM\Column(type="smallint")
     */
    private $volonte;

    /**
     * @ORM\Column(type="smallint")
     */
    private $reflexes;

    /**
     * @ORM\Column(type="smallint")
     */
    private $intuition;

    /**
     * @ORM\Column(type="smallint")
     */
    private $agilite;

    /**
     * @ORM\Column(type="smallint")
     */
    private $intelligence;

    /**
     * @ORM\Column(type="smallint")
     */
    private $forceStat;

    /**
     * @ORM\Column(type="smallint")
     */
    private $perception;

    /**
     * @ORM\Column(type="smallint")
     */
    private $vide;

    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence1;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur1;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence2;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur2;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence3;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur3;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence4;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur4;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence5;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur5;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence6;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur6;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence7;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur7;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence8;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur8;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence9;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur9;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence10;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur10;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence11;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur11;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence12;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur12;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence13;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur13;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence14;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur14;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence15;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur15;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence16;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur16;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence17;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur17;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence18;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur18;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence19;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur19;
    /** @ORM\ManyToOne(targetEntity=Competence::class) @ORM\JoinColumn(nullable=true) */ private $competence20;
    /** @ORM\Column(type="smallint", nullable=true) */ private $valeur20;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonnage(): ?Personnage
    {
        return $this->personnage;
    }

    public function setPersonnage(Personnage $personnage): self
    {
        $this->personnage = $personnage;

        return $this;
    }

    public function getCreationExp(): ?int
    {
        return $this->creationExp;
    }

    public function setCreationExp(int $creationExp): self
    {
        $this->creationExp = $creationExp;

        return $this;
    }

    public function getAvantage1(): ?Avantage { return $this->avantage1; }
    public function setAvantage1(?Avantage $a): self { $this->avantage1 = $a; return $this; }

    public function getAvantage2(): ?Avantage { return $this->avantage2; }
    public function setAvantage2(?Avantage $a): self { $this->avantage2 = $a; return $this; }

    public function getDesavantage1(): ?Avantage { return $this->desavantage1; }
    public function setDesavantage1(?Avantage $a): self { $this->desavantage1 = $a; return $this; }

    public function getDesavantage2(): ?Avantage { return $this->desavantage2; }
    public function setDesavantage2(?Avantage $a): self { $this->desavantage2 = $a; return $this; }

    public function getArme(): ?Objet { return $this->arme; }
    public function setArme(?Objet $a): self { $this->arme = $a; return $this; }

    public function getArmure(): ?Objet { return $this->armure; }
    public function setArmure(?Objet $a): self { $this->armure = $a; return $this; }

    public function getReductionSpecial(): ?int { return $this->reductionSpecial; }
    public function setReductionSpecial(?int $r): self { $this->reductionSpecial = $r; return $this; }

    public function getHonneur(): ?string { return $this->honneur; }
    public function setHonneur(?string $h): self { $this->honneur = $h; return $this; }

    public function getGloire(): ?string { return $this->gloire; }
    public function setGloire(?string $g): self { $this->gloire = $g; return $this; }

    public function getInfamie(): ?string { return $this->infamie; }
    public function setInfamie(?string $i): self { $this->infamie = $i; return $this; }

    public function getSouillure(): ?string { return $this->souillure; }
    public function setSouillure(?string $s): self { $this->souillure = $s; return $this; }

    public function getConstitution(): ?int
    {
        return $this->constitution;
    }

    public function setConstitution(int $constitution): self
    {
        $this->constitution = $constitution;

        return $this;
    }

    public function getVolonte(): ?int
    {
        return $this->volonte;
    }

    public function setVolonte(int $volonte): self
    {
        $this->volonte = $volonte;

        return $this;
    }

    public function getReflexes(): ?int
    {
        return $this->reflexes;
    }

    public function setReflexes(int $reflexes): self
    {
        $this->reflexes = $reflexes;

        return $this;
    }

    public function getIntuition(): ?int
    {
        return $this->intuition;
    }

    public function setIntuition(int $intuition): self
    {
        $this->intuition = $intuition;

        return $this;
    }

    public function getAgilite(): ?int
    {
        return $this->agilite;
    }

    public function setAgilite(int $agilite): self
    {
        $this->agilite = $agilite;

        return $this;
    }

    public function getIntelligence(): ?int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): self
    {
        $this->intelligence = $intelligence;

        return $this;
    }

    public function getForceStat(): ?int
    {
        return $this->forceStat;
    }

    public function setForceStat(int $forceStat): self
    {
        $this->forceStat = $forceStat;

        return $this;
    }

    public function getPerception(): ?int
    {
        return $this->perception;
    }

    public function setPerception(int $perception): self
    {
        $this->perception = $perception;

        return $this;
    }

    public function getVide(): ?int
    {
        return $this->vide;
    }

    public function setVide(int $vide): self
    {
        $this->vide = $vide;

        return $this;
    }

    public function getCompetence1(): ?Competence { return $this->competence1; }
    public function setCompetence1(?Competence $c): self { $this->competence1 = $c; return $this; }
    public function getValeur1(): ?int { return $this->valeur1; }
    public function setValeur1(?int $v): self { $this->valeur1 = $v; return $this; }

    public function getCompetence2(): ?Competence { return $this->competence2; }
    public function setCompetence2(?Competence $c): self { $this->competence2 = $c; return $this; }
    public function getValeur2(): ?int { return $this->valeur2; }
    public function setValeur2(?int $v): self { $this->valeur2 = $v; return $this; }

    public function getCompetence3(): ?Competence { return $this->competence3; }
    public function setCompetence3(?Competence $c): self { $this->competence3 = $c; return $this; }
    public function getValeur3(): ?int { return $this->valeur3; }
    public function setValeur3(?int $v): self { $this->valeur3 = $v; return $this; }

    public function getCompetence4(): ?Competence { return $this->competence4; }
    public function setCompetence4(?Competence $c): self { $this->competence4 = $c; return $this; }
    public function getValeur4(): ?int { return $this->valeur4; }
    public function setValeur4(?int $v): self { $this->valeur4 = $v; return $this; }

    public function getCompetence5(): ?Competence { return $this->competence5; }
    public function setCompetence5(?Competence $c): self { $this->competence5 = $c; return $this; }
    public function getValeur5(): ?int { return $this->valeur5; }
    public function setValeur5(?int $v): self { $this->valeur5 = $v; return $this; }

    public function getCompetence6(): ?Competence { return $this->competence6; }
    public function setCompetence6(?Competence $c): self { $this->competence6 = $c; return $this; }
    public function getValeur6(): ?int { return $this->valeur6; }
    public function setValeur6(?int $v): self { $this->valeur6 = $v; return $this; }

    public function getCompetence7(): ?Competence { return $this->competence7; }
    public function setCompetence7(?Competence $c): self { $this->competence7 = $c; return $this; }
    public function getValeur7(): ?int { return $this->valeur7; }
    public function setValeur7(?int $v): self { $this->valeur7 = $v; return $this; }

    public function getCompetence8(): ?Competence { return $this->competence8; }
    public function setCompetence8(?Competence $c): self { $this->competence8 = $c; return $this; }
    public function getValeur8(): ?int { return $this->valeur8; }
    public function setValeur8(?int $v): self { $this->valeur8 = $v; return $this; }

    public function getCompetence9(): ?Competence { return $this->competence9; }
    public function setCompetence9(?Competence $c): self { $this->competence9 = $c; return $this; }
    public function getValeur9(): ?int { return $this->valeur9; }
    public function setValeur9(?int $v): self { $this->valeur9 = $v; return $this; }

    public function getCompetence10(): ?Competence { return $this->competence10; }
    public function setCompetence10(?Competence $c): self { $this->competence10 = $c; return $this; }
    public function getValeur10(): ?int { return $this->valeur10; }
    public function setValeur10(?int $v): self { $this->valeur10 = $v; return $this; }

    public function getCompetence11(): ?Competence { return $this->competence11; }
    public function setCompetence11(?Competence $c): self { $this->competence11 = $c; return $this; }
    public function getValeur11(): ?int { return $this->valeur11; }
    public function setValeur11(?int $v): self { $this->valeur11 = $v; return $this; }

    public function getCompetence12(): ?Competence { return $this->competence12; }
    public function setCompetence12(?Competence $c): self { $this->competence12 = $c; return $this; }
    public function getValeur12(): ?int { return $this->valeur12; }
    public function setValeur12(?int $v): self { $this->valeur12 = $v; return $this; }

    public function getCompetence13(): ?Competence { return $this->competence13; }
    public function setCompetence13(?Competence $c): self { $this->competence13 = $c; return $this; }
    public function getValeur13(): ?int { return $this->valeur13; }
    public function setValeur13(?int $v): self { $this->valeur13 = $v; return $this; }

    public function getCompetence14(): ?Competence { return $this->competence14; }
    public function setCompetence14(?Competence $c): self { $this->competence14 = $c; return $this; }
    public function getValeur14(): ?int { return $this->valeur14; }
    public function setValeur14(?int $v): self { $this->valeur14 = $v; return $this; }

    public function getCompetence15(): ?Competence { return $this->competence15; }
    public function setCompetence15(?Competence $c): self { $this->competence15 = $c; return $this; }
    public function getValeur15(): ?int { return $this->valeur15; }
    public function setValeur15(?int $v): self { $this->valeur15 = $v; return $this; }

    public function getCompetence16(): ?Competence { return $this->competence16; }
    public function setCompetence16(?Competence $c): self { $this->competence16 = $c; return $this; }
    public function getValeur16(): ?int { return $this->valeur16; }
    public function setValeur16(?int $v): self { $this->valeur16 = $v; return $this; }

    public function getCompetence17(): ?Competence { return $this->competence17; }
    public function setCompetence17(?Competence $c): self { $this->competence17 = $c; return $this; }
    public function getValeur17(): ?int { return $this->valeur17; }
    public function setValeur17(?int $v): self { $this->valeur17 = $v; return $this; }

    public function getCompetence18(): ?Competence { return $this->competence18; }
    public function setCompetence18(?Competence $c): self { $this->competence18 = $c; return $this; }
    public function getValeur18(): ?int { return $this->valeur18; }
    public function setValeur18(?int $v): self { $this->valeur18 = $v; return $this; }

    public function getCompetence19(): ?Competence { return $this->competence19; }
    public function setCompetence19(?Competence $c): self { $this->competence19 = $c; return $this; }
    public function getValeur19(): ?int { return $this->valeur19; }
    public function setValeur19(?int $v): self { $this->valeur19 = $v; return $this; }

    public function getCompetence20(): ?Competence { return $this->competence20; }
    public function setCompetence20(?Competence $c): self { $this->competence20 = $c; return $this; }
    public function getValeur20(): ?int { return $this->valeur20; }
    public function setValeur20(?int $v): self { $this->valeur20 = $v; return $this; }
}
