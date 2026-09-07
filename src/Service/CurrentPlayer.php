<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;

class CurrentPlayer
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function isConnected(): bool
    {
        return $this->security->getUser() !== null;
    }

    public function characters(): array
    {
        $user = $this->security->getUser();

        if ($user === null || !method_exists($user, 'getPersonnages')) {
            return [];
        }

        if ($user->isWithoutCharacter()) {
            return [];
        }

        if ($user->getMainCharacter() !== null) {
            return [$user->getMainCharacter()];
        }

        $dernier = null;
        foreach ($user->getPersonnages() as $personnage) {
            if ($dernier === null || $personnage->getId() > $dernier->getId()) {
                $dernier = $personnage;
            }
        }

        return $dernier === null ? [] : [$dernier];
    }

    public function characterIds(): array
    {
        $ids = [];
        foreach ($this->characters() as $personnage) {
            $ids[] = $personnage->getId();
        }

        return $ids;
    }
}
