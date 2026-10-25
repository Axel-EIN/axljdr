<?php

namespace App\Service;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Security;

class CurrentPlayer
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function navigatingCharacter(): ?Personnage
    {
        return $this->seesAll() ? null : $this->mainCharacter();
    }

    public function mainCharacter(): ?Personnage
    {
        $user = $this->security->getUser();

        if (!$user instanceof Utilisateur) {
            return null;
        }

        if ($user->getMainCharacter() !== null) {
            return $user->getMainCharacter();
        }

        $dernier = null;
        foreach ($user->getPersonnages() as $personnage) {
            if ($dernier === null || $personnage->getId() > $dernier->getId()) {
                $dernier = $personnage;
            }
        }

        return $dernier;
    }

    public function seesAll(): bool
    {
        return $this->security->isGranted('ROLE_MJ');
    }

    public function characterIds(): array
    {
        $personnage = $this->navigatingCharacter();

        return $personnage === null ? [] : [$personnage->getId()];
    }
}
