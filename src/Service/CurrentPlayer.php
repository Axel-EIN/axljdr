<?php

namespace App\Service;

use App\Entity\Personnage;
use Symfony\Component\Security\Core\Security;

class CurrentPlayer
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function main(): ?Personnage
    {
        $user = $this->security->getUser();

        if ($user === null || !method_exists($user, 'getPersonnages') || $this->isWithoutCharacter()) {
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
        return $this->security->isGranted('ROLE_MJ') && !$this->isWithoutCharacter();
    }

    public function characterIds(): array
    {
        $personnage = $this->main();

        return $personnage === null ? [] : [$personnage->getId()];
    }

    private function isWithoutCharacter(): bool
    {
        $user = $this->security->getUser();

        return $user !== null
            && method_exists($user, 'isWithoutCharacter')
            && $user->isWithoutCharacter()
            && $this->security->isGranted('ROLE_MJ');
    }
}
