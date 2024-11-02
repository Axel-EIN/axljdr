<?php

namespace App\Entity;

use App\Repository\DevelopmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DevelopmentRepository::class)
 */
class Development
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
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $resume;

    /**
     * @ORM\ManyToOne(targetEntity=personnage::class, inversedBy="developments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $protagonist;

    /**
     * @ORM\ManyToOne(targetEntity=scene::class, inversedBy="developments")
     */
    private $scene;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function getProtagonist(): ?personnage
    {
        return $this->protagonist;
    }

    public function setProtagonist(?personnage $protagonist): self
    {
        $this->protagonist = $protagonist;

        return $this;
    }

    public function getScene(): ?scene
    {
        return $this->scene;
    }

    public function setScene(?scene $scene): self
    {
        $this->scene = $scene;

        return $this;
    }
}
