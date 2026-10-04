<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Personnage;
use App\Repository\UnlockRepository;
use Symfony\Component\Security\Core\Security;

class Visibility
{
    public const HIDDEN = 'hidden';
    public const SECRET = 'secret';
    public const DISCOVERED = 'discovered';
    public const LOCKED = 'locked';
    public const UNLOCKED = 'unlocked';
    public const COMMON = 'common';
    public const COMMON_DISCOVERED = 'common-discovered';
    public const PUBLIC = 'public';

    public const FRAICHEUR_JOURS = 14;

    private const READABLE = [self::PUBLIC, self::UNLOCKED, self::DISCOVERED, self::COMMON_DISCOVERED];

    private const UNSEEN = [self::HIDDEN, self::SECRET, self::COMMON];

    private const REVEALED = [
        Access::SECRET => self::DISCOVERED,
        Access::LOCKED => self::UNLOCKED,
        Access::COMMON => self::COMMON_DISCOVERED,
    ];

    private const PALIERS = [
        Access::SECRET => self::SECRET,
        Access::LOCKED => self::LOCKED,
        Access::COMMON => self::COMMON,
        Access::PUBLIC => self::PUBLIC,
    ];

    private $security;
    private $currentPlayer;
    private $unlockRepository;
    private $unlocks;
    private $owned;

    public function __construct(Security $security, CurrentPlayer $currentPlayer, UnlockRepository $unlockRepository)
    {
        $this->security = $security;
        $this->currentPlayer = $currentPlayer;
        $this->unlockRepository = $unlockRepository;
    }

    public function state($element, bool $revealAuto = false): string
    {
        $access = $this->accessOf($element);

        if ($this->isOwned($element)) {
            return Access::needsUnlock($access) ? self::REVEALED[$access] : self::PUBLIC;
        }

        if (Access::needsUnlock($access) && $this->isUnlocked($element)) {
            return self::REVEALED[$access];
        }

        if ($this->security->isGranted('ROLE_MJ')) {
            return self::PALIERS[$access];
        }

        if ($access === Access::PUBLIC) {
            return $this->isPublished($element) ? self::PUBLIC : self::HIDDEN;
        }

        if ($revealAuto && $access === Access::COMMON && Access::unlocksByMeeting($element)) {
            return self::COMMON;
        }

        return Access::isHidden($access) ? self::HIDDEN : self::LOCKED;
    }

    public function isUnseen($element): bool
    {
        return in_array($this->state($element), self::UNSEEN, true);
    }

    public function isReadable($element): bool
    {
        return $this->security->isGranted('ROLE_MJ')
            || in_array($this->state($element), self::READABLE, true);
    }

    public function sortByAccess(iterable $elements): array
    {
        $elements = is_array($elements) ? array_values($elements) : iterator_to_array($elements, false);

        usort($elements, function ($a, $b) {
            $lisibleA = in_array($this->state($a), self::READABLE, true);
            $lisibleB = in_array($this->state($b), self::READABLE, true);

            if ($lisibleA !== $lisibleB) {
                return $lisibleA ? -1 : 1;
            }

            return $lisibleA
                ? $this->accessOf($a) <=> $this->accessOf($b)
                : $this->accessOf($b) <=> $this->accessOf($a);
        });

        return $elements;
    }

    public function isListed($element, bool $revealAuto = false): bool
    {
        return $this->security->isGranted('ROLE_MJ') || $this->state($element, $revealAuto) !== self::HIDDEN;
    }

    public function listedOnly(iterable $elements): array
    {
        $elements = is_array($elements) ? $elements : iterator_to_array($elements, false);

        return array_values(array_filter($elements, function ($element) {
            return $this->isListed($element);
        }));
    }

    public function discoveredAt($element): ?\DateTimeInterface
    {
        $personnel = $this->unlockedAt($element);

        if ($personnel !== null) {
            return $personnel;
        }

        return is_object($element) && method_exists($element, 'getPublishedAt') ? $element->getPublishedAt() : null;
    }

    public function isFresh($element): bool
    {
        if (!in_array($this->state($element), self::READABLE, true)) {
            return false;
        }

        $date = $this->discoveredAt($element);

        return $date !== null
            && $date <= new \DateTime()
            && $date >= new \DateTime('-' . self::FRAICHEUR_JOURS . ' days');
    }

    public function unlockedIds(string $key): array
    {
        return array_keys($this->unlocks()[$key] ?? []);
    }

    public function unlockedAt($element): ?\DateTimeInterface
    {
        $key = EntityRegistry::keyOf($element);

        return $key === null ? null : ($this->unlocks()[$key][$element->getId()] ?? null);
    }

    private function accessOf($element): int
    {
        if (!is_object($element) || !method_exists($element, 'getAccess')) {
            return Access::PUBLIC;
        }

        return (int) $element->getAccess();
    }

    private function isPublished($element): bool
    {
        if (!is_object($element) || !method_exists($element, 'getPublishedAt')) {
            return true;
        }

        $publie = $element->getPublishedAt();

        return $publie === null || $publie <= new \DateTime();
    }

    private function isUnlocked($element): bool
    {
        return $this->unlockedAt($element) !== null;
    }

    private function isOwned($element): bool
    {
        $key = EntityRegistry::keyOf($element);

        return $key !== null && isset($this->owned()[$key][$element->getId()]);
    }

    private function owned(): array
    {
        if ($this->owned !== null) {
            return $this->owned;
        }

        $this->owned = [];

        foreach ($this->currentPlayer->allCharacters() as $personnage) {
            foreach ($this->possessionsOf($personnage) as $element) {
                $key = EntityRegistry::keyOf($element);

                if ($key !== null) {
                    $this->owned[$key][$element->getId()] = true;
                }
            }
        }

        return $this->owned;
    }

    private function possessionsOf(Personnage $personnage): array
    {
        $possessions = [$personnage, $personnage->getEcole(), $personnage->getClan()];
        $fiche = $personnage->getFichePersonnage();

        if ($fiche === null) {
            return $possessions;
        }

        $competences = [$fiche->getCompCombatActuelle()];
        for ($i = 1; $i <= 20; $i++) {
            $competences[] = $fiche->{'getCompetence' . $i}();
        }

        return array_merge(
            $possessions,
            [$fiche->getArme(), $fiche->getArme2(), $fiche->getArmeActuelle(), $fiche->getArmure()],
            [$fiche->getAvantage1(), $fiche->getAvantage2(), $fiche->getDesavantage1(), $fiche->getDesavantage2()],
            $competences,
            $fiche->getInventoryItems()->toArray(),
            $fiche->getKnownSpells()->toArray()
        );
    }

    private function unlocks(): array
    {
        if ($this->unlocks !== null) {
            return $this->unlocks;
        }

        return $this->unlocks = $this->unlockRepository->mapForCharacters($this->currentPlayer->characterIds());
    }
}
