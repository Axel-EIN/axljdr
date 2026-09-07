<?php

namespace App\Entity;

use App\Repository\UnlockRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UnlockRepository::class)
 * @ORM\Table(
 *   name="character_unlock",
 *   uniqueConstraints={@ORM\UniqueConstraint(name="unlock_element_character", columns={"character_id", "entity", "element_id"})}
 * )
 */
class Unlock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Personnage::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $character;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $entity;

    /**
     * @ORM\Column(type="integer")
     */
    private $elementId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $unlockedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $byMeeting = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): ?Personnage
    {
        return $this->character;
    }

    public function setCharacter(?Personnage $character): self
    {
        $this->character = $character;

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

    public function getElementId(): ?int
    {
        return $this->elementId;
    }

    public function setElementId(int $elementId): self
    {
        $this->elementId = $elementId;

        return $this;
    }

    public function getUnlockedAt(): ?\DateTimeInterface
    {
        return $this->unlockedAt;
    }

    public function setUnlockedAt(\DateTimeInterface $unlockedAt): self
    {
        $this->unlockedAt = $unlockedAt;

        return $this;
    }

    public function isByMeeting(): bool
    {
        return $this->byMeeting;
    }

    public function setByMeeting(bool $byMeeting): self
    {
        $this->byMeeting = $byMeeting;

        return $this;
    }
}
