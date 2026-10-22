<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LieuRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Lieu
{
  use PublishableTrait;

  public const TYPE_ICONS = [
    'Cité' => 'fa-solid fa-landmark',
    'Ville' => 'fa-solid fa-archway',
    'Village' => 'fa-solid fa-house-chimney',
    'Palais Fortifié' => 'fa-solid fa-gopuram',
    'Forteresse' => 'fa-brands fa-fort-awesome',
    'Donjon' => 'fa-solid fa-dungeon',
    'Palais' => 'fa-solid fa-vihara',
    'Temple' => 'fa-solid fa-torii-gate',
    'Région' => 'fa-solid fa-mountain-sun',
    'Royaume Spirituel' => 'fa-solid fa-yin-yang',
    'Structure' => 'fa-solid fa-monument',
  ];

  public const CITY_TYPES = ['Cité', 'Ville'];

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
  private $planMap;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $description;

  /**
   * @ORM\ManyToOne(targetEntity=Clan::class, inversedBy="lieux")
   */
  private $clan;

  /**
   * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
   */
  private $locX;

  /**
   * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
   * @Assert\Range(min=1, max=99)
   */
  private $locY;

  /**
   * @ORM\Column(type="decimal", precision=3, scale=2, nullable=true)
   * @Assert\Range(min=0.25, max=2)
   */
  private $zoom;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $surnom;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $icone;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $planDesc;

  /**
   * @ORM\OneToMany(targetEntity=Scene::class, mappedBy="lieu")
   */
  private $scenes;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $type;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  private $population;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $regionDesc;

  public function __construct()
  {
    $this->scenes = new ArrayCollection();
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

  public function getImage(): ?string
  {
    return $this->image;
  }

  public function setImage(?string $image): self
  {
    $this->image = $image;

    return $this;
  }

  public function getPlanMap(): ?string
  {
    return $this->planMap;
  }

  public function setPlanMap(?string $planMap): self
  {
    $this->planMap = $planMap;

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

  public function getClan(): ?Clan
  {
    return $this->clan;
  }

  public function setClan(?Clan $clan): self
  {
    $this->clan = $clan;

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

  public function getZoom(): ?string
  {
    return $this->zoom;
  }

  public function setZoom(?string $zoom): self
  {
    $this->zoom = $zoom;

    return $this;
  }

  public function getSurnom(): ?string
  {
    return $this->surnom;
  }

  public function setSurnom(?string $surnom): self
  {
    $this->surnom = $surnom;

    return $this;
  }

  public function getIcone(): ?string
  {
    return $this->icone;
  }

  public function setIcone(?string $icone): self
  {
    $this->icone = $icone;

    return $this;
  }

  public function getPlanDesc(): ?string
  {
    return $this->planDesc;
  }

  public function setPlanDesc(?string $planDesc): self
  {
    $this->planDesc = $planDesc;

    return $this;
  }

  /**
   * @return Collection<int, Scene>
   */
  public function getScenes(): Collection
  {
    return $this->scenes;
  }

  public function addScene(Scene $scene): self
  {
    if (!$this->scenes->contains($scene)) {
      $this->scenes[] = $scene;
      $scene->setLieu($this);
    }

    return $this;
  }

  public function removeScene(Scene $scene): self
  {
    if ($this->scenes->removeElement($scene)) {
      if ($scene->getLieu() === $this) {
        $scene->setLieu(null);
      }
    }

    return $this;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): self
  {
    $this->type = $type;

    return $this;
  }

  public function getTypeIcon(): ?string
  {
    return self::TYPE_ICONS[$this->type] ?? null;
  }

  public function isCity(): bool
  {
    return in_array($this->type, self::CITY_TYPES, true);
  }

  public function getPopulation(): ?int
  {
    return $this->population;
  }

  public function setPopulation(?int $population): self
  {
    $this->population = $population;

    return $this;
  }

  public function getRegionDesc(): ?string
  {
    return $this->regionDesc;
  }

  public function setRegionDesc(?string $regionDesc): self
  {
    $this->regionDesc = $regionDesc;

    return $this;
  }
}
