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
    private $arme2;

    /**
     * @ORM\ManyToOne(targetEntity=Objet::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $armeActuelle;

    /**
     * @ORM\ManyToOne(targetEntity=Competence::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $compCombatActuelle;

    /**
     * @ORM\ManyToOne(targetEntity=Objet::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $armure;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reductionModifier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ndModifier;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $dmgModifier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $initiativeModifier;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $attaqueModifier;

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

    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations1;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations2;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations3;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations4;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations5;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations6;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations7;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations8;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations9;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations10;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations11;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations12;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations13;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations14;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations15;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations16;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations17;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations18;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations19;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $specialisations20;

    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole1;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole2;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole3;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole4;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole5;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole6;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole7;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole8;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole9;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole10;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole11;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole12;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole13;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole14;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole15;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole16;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole17;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole18;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole19;
    /** @ORM\Column(type="smallint", nullable=true) */ private $compEcole20;

    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole1;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole2;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole3;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole4;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole5;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole6;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole7;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole8;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole9;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole10;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole11;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole12;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole13;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole14;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole15;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole16;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole17;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole18;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole19;
    /** @ORM\Column(type="string", length=6, nullable=true) */ private $speEcole20;

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

    public function getArme2(): ?Objet { return $this->arme2; }
    public function setArme2(?Objet $a): self { $this->arme2 = $a; return $this; }

    public function getArmeActuelle(): ?Objet { return $this->armeActuelle; }
    public function setArmeActuelle(?Objet $a): self { $this->armeActuelle = $a; return $this; }

    public function getCompCombatActuelle(): ?Competence { return $this->compCombatActuelle; }
    public function setCompCombatActuelle(?Competence $c): self { $this->compCombatActuelle = $c; return $this; }

    public function getArmure(): ?Objet { return $this->armure; }
    public function setArmure(?Objet $a): self { $this->armure = $a; return $this; }

    public function getReductionModifier(): ?int { return $this->reductionModifier; }
    public function setReductionModifier(?int $r): self { $this->reductionModifier = $r; return $this; }

    public function getNdModifier(): ?int { return $this->ndModifier; }
    public function setNdModifier(?int $n): self { $this->ndModifier = $n; return $this; }

    public function getDmgModifier(): ?string { return $this->dmgModifier; }
    public function setDmgModifier(?string $m): self { $this->dmgModifier = $m; return $this; }

    public function getInitiativeModifier(): ?int { return $this->initiativeModifier; }
    public function setInitiativeModifier(?int $m): self { $this->initiativeModifier = $m; return $this; }

    public function getAttaqueModifier(): ?string { return $this->attaqueModifier; }
    public function setAttaqueModifier(?string $m): self { $this->attaqueModifier = $m; return $this; }

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

    public function getSpecialisations1(): ?string { return $this->specialisations1; }
    public function setSpecialisations1(?string $s): self { $this->specialisations1 = $s; return $this; }
    public function getSpecialisations2(): ?string { return $this->specialisations2; }
    public function setSpecialisations2(?string $s): self { $this->specialisations2 = $s; return $this; }
    public function getSpecialisations3(): ?string { return $this->specialisations3; }
    public function setSpecialisations3(?string $s): self { $this->specialisations3 = $s; return $this; }
    public function getSpecialisations4(): ?string { return $this->specialisations4; }
    public function setSpecialisations4(?string $s): self { $this->specialisations4 = $s; return $this; }
    public function getSpecialisations5(): ?string { return $this->specialisations5; }
    public function setSpecialisations5(?string $s): self { $this->specialisations5 = $s; return $this; }
    public function getSpecialisations6(): ?string { return $this->specialisations6; }
    public function setSpecialisations6(?string $s): self { $this->specialisations6 = $s; return $this; }
    public function getSpecialisations7(): ?string { return $this->specialisations7; }
    public function setSpecialisations7(?string $s): self { $this->specialisations7 = $s; return $this; }
    public function getSpecialisations8(): ?string { return $this->specialisations8; }
    public function setSpecialisations8(?string $s): self { $this->specialisations8 = $s; return $this; }
    public function getSpecialisations9(): ?string { return $this->specialisations9; }
    public function setSpecialisations9(?string $s): self { $this->specialisations9 = $s; return $this; }
    public function getSpecialisations10(): ?string { return $this->specialisations10; }
    public function setSpecialisations10(?string $s): self { $this->specialisations10 = $s; return $this; }
    public function getSpecialisations11(): ?string { return $this->specialisations11; }
    public function setSpecialisations11(?string $s): self { $this->specialisations11 = $s; return $this; }
    public function getSpecialisations12(): ?string { return $this->specialisations12; }
    public function setSpecialisations12(?string $s): self { $this->specialisations12 = $s; return $this; }
    public function getSpecialisations13(): ?string { return $this->specialisations13; }
    public function setSpecialisations13(?string $s): self { $this->specialisations13 = $s; return $this; }
    public function getSpecialisations14(): ?string { return $this->specialisations14; }
    public function setSpecialisations14(?string $s): self { $this->specialisations14 = $s; return $this; }
    public function getSpecialisations15(): ?string { return $this->specialisations15; }
    public function setSpecialisations15(?string $s): self { $this->specialisations15 = $s; return $this; }
    public function getSpecialisations16(): ?string { return $this->specialisations16; }
    public function setSpecialisations16(?string $s): self { $this->specialisations16 = $s; return $this; }
    public function getSpecialisations17(): ?string { return $this->specialisations17; }
    public function setSpecialisations17(?string $s): self { $this->specialisations17 = $s; return $this; }
    public function getSpecialisations18(): ?string { return $this->specialisations18; }
    public function setSpecialisations18(?string $s): self { $this->specialisations18 = $s; return $this; }
    public function getSpecialisations19(): ?string { return $this->specialisations19; }
    public function setSpecialisations19(?string $s): self { $this->specialisations19 = $s; return $this; }
    public function getSpecialisations20(): ?string { return $this->specialisations20; }
    public function setSpecialisations20(?string $s): self { $this->specialisations20 = $s; return $this; }

    public function getCompEcole1(): ?int { return $this->compEcole1; }
    public function setCompEcole1($v): self { $this->compEcole1 = ($v === null || $v === '') ? null : (int) $v; return $this; }
    public function getCompEcole2(): ?int { return $this->compEcole2; }
    public function setCompEcole2($v): self { $this->compEcole2 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole3(): ?int { return $this->compEcole3; }
    public function setCompEcole3($v): self { $this->compEcole3 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole4(): ?int { return $this->compEcole4; }
    public function setCompEcole4($v): self { $this->compEcole4 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole5(): ?int { return $this->compEcole5; }
    public function setCompEcole5($v): self { $this->compEcole5 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole6(): ?int { return $this->compEcole6; }
    public function setCompEcole6($v): self { $this->compEcole6 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole7(): ?int { return $this->compEcole7; }
    public function setCompEcole7($v): self { $this->compEcole7 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole8(): ?int { return $this->compEcole8; }
    public function setCompEcole8($v): self { $this->compEcole8 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole9(): ?int { return $this->compEcole9; }
    public function setCompEcole9($v): self { $this->compEcole9 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole10(): ?int { return $this->compEcole10; }
    public function setCompEcole10($v): self { $this->compEcole10 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole11(): ?int { return $this->compEcole11; }
    public function setCompEcole11($v): self { $this->compEcole11 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole12(): ?int { return $this->compEcole12; }
    public function setCompEcole12($v): self { $this->compEcole12 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole13(): ?int { return $this->compEcole13; }
    public function setCompEcole13($v): self { $this->compEcole13 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole14(): ?int { return $this->compEcole14; }
    public function setCompEcole14($v): self { $this->compEcole14 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole15(): ?int { return $this->compEcole15; }
    public function setCompEcole15($v): self { $this->compEcole15 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole16(): ?int { return $this->compEcole16; }
    public function setCompEcole16($v): self { $this->compEcole16 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole17(): ?int { return $this->compEcole17; }
    public function setCompEcole17($v): self { $this->compEcole17 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole18(): ?int { return $this->compEcole18; }
    public function setCompEcole18($v): self { $this->compEcole18 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole19(): ?int { return $this->compEcole19; }
    public function setCompEcole19($v): self { $this->compEcole19 = ($v === null || $v === "") ? null : (int) $v; return $this; }
    public function getCompEcole20(): ?int { return $this->compEcole20; }
    public function setCompEcole20($v): self { $this->compEcole20 = ($v === null || $v === "") ? null : (int) $v; return $this; }

    public function getSpeEcole1(): ?string { return $this->speEcole1; }
    public function setSpeEcole1(?string $s): self { $this->speEcole1 = $s; return $this; }
    public function getSpeEcole2(): ?string { return $this->speEcole2; }
    public function setSpeEcole2(?string $s): self { $this->speEcole2 = $s; return $this; }
    public function getSpeEcole3(): ?string { return $this->speEcole3; }
    public function setSpeEcole3(?string $s): self { $this->speEcole3 = $s; return $this; }
    public function getSpeEcole4(): ?string { return $this->speEcole4; }
    public function setSpeEcole4(?string $s): self { $this->speEcole4 = $s; return $this; }
    public function getSpeEcole5(): ?string { return $this->speEcole5; }
    public function setSpeEcole5(?string $s): self { $this->speEcole5 = $s; return $this; }
    public function getSpeEcole6(): ?string { return $this->speEcole6; }
    public function setSpeEcole6(?string $s): self { $this->speEcole6 = $s; return $this; }
    public function getSpeEcole7(): ?string { return $this->speEcole7; }
    public function setSpeEcole7(?string $s): self { $this->speEcole7 = $s; return $this; }
    public function getSpeEcole8(): ?string { return $this->speEcole8; }
    public function setSpeEcole8(?string $s): self { $this->speEcole8 = $s; return $this; }
    public function getSpeEcole9(): ?string { return $this->speEcole9; }
    public function setSpeEcole9(?string $s): self { $this->speEcole9 = $s; return $this; }
    public function getSpeEcole10(): ?string { return $this->speEcole10; }
    public function setSpeEcole10(?string $s): self { $this->speEcole10 = $s; return $this; }
    public function getSpeEcole11(): ?string { return $this->speEcole11; }
    public function setSpeEcole11(?string $s): self { $this->speEcole11 = $s; return $this; }
    public function getSpeEcole12(): ?string { return $this->speEcole12; }
    public function setSpeEcole12(?string $s): self { $this->speEcole12 = $s; return $this; }
    public function getSpeEcole13(): ?string { return $this->speEcole13; }
    public function setSpeEcole13(?string $s): self { $this->speEcole13 = $s; return $this; }
    public function getSpeEcole14(): ?string { return $this->speEcole14; }
    public function setSpeEcole14(?string $s): self { $this->speEcole14 = $s; return $this; }
    public function getSpeEcole15(): ?string { return $this->speEcole15; }
    public function setSpeEcole15(?string $s): self { $this->speEcole15 = $s; return $this; }
    public function getSpeEcole16(): ?string { return $this->speEcole16; }
    public function setSpeEcole16(?string $s): self { $this->speEcole16 = $s; return $this; }
    public function getSpeEcole17(): ?string { return $this->speEcole17; }
    public function setSpeEcole17(?string $s): self { $this->speEcole17 = $s; return $this; }
    public function getSpeEcole18(): ?string { return $this->speEcole18; }
    public function setSpeEcole18(?string $s): self { $this->speEcole18 = $s; return $this; }
    public function getSpeEcole19(): ?string { return $this->speEcole19; }
    public function setSpeEcole19(?string $s): self { $this->speEcole19 = $s; return $this; }
    public function getSpeEcole20(): ?string { return $this->speEcole20; }
    public function setSpeEcole20(?string $s): self { $this->speEcole20 = $s; return $this; }
}
