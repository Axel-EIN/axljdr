<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait PartLinksTrait
{
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $partLinks = [];

    public function getPartLinks(int $part): array
    {
        return $this->partLinks[$part] ?? [];
    }

    public function setPartLinks(int $part, ?array $links): self
    {
        $partLinks = $this->partLinks ?? [];
        $partLinks[$part] = array_values($links ?? []);
        $this->partLinks = array_filter($partLinks);

        return $this;
    }
}
