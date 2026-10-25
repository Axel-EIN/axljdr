<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Personnage;
use App\Repository\UnlockRepository;

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

    public const FRESHNESS_DAYS = 14;

    private const READABLE = [self::PUBLIC, self::UNLOCKED, self::DISCOVERED, self::COMMON_DISCOVERED];

    private const UNSEEN = [self::HIDDEN, self::SECRET, self::COMMON];

    private const REVEALED = [
        Access::SECRET => self::DISCOVERED,
        Access::LOCKED => self::UNLOCKED,
        Access::COMMON => self::COMMON_DISCOVERED,
    ];

    private const LEVELS = [
        Access::SECRET => self::SECRET,
        Access::LOCKED => self::LOCKED,
        Access::COMMON => self::COMMON,
        Access::PUBLIC => self::PUBLIC,
    ];

    private $currentPlayer;
    private $unlockRepository;
    private $unlocks;
    private $owned;

    public function __construct(CurrentPlayer $currentPlayer, UnlockRepository $unlockRepository)
    {
        $this->currentPlayer = $currentPlayer;
        $this->unlockRepository = $unlockRepository;
    }

    public function state($element, bool $revealSession = false): string
    {
        $access = $this->accessOf($element);

        if ($this->isOwned($element)) {
            return Access::needsUnlock($access) ? self::REVEALED[$access] : self::PUBLIC;
        }

        if (Access::needsUnlock($access) && $this->isUnlocked($element)) {
            return self::REVEALED[$access];
        }

        if ($this->currentPlayer->seesAll()) {
            return self::LEVELS[$access];
        }

        if ($access === Access::PUBLIC) {
            return $this->isPublished($element) ? self::PUBLIC : self::HIDDEN;
        }

        if ($revealSession && !$this->isIndividualSecret($element)) {
            return self::LEVELS[$access];
        }

        return Access::isHidden($access) ? self::HIDDEN : self::LOCKED;
    }

    public function isIndividuallyRevealed($element): bool
    {
        return Access::isHidden($this->accessOf($element))
            && $element->isManualUnlock()
            && in_array($this->state($element), self::READABLE, true);
    }

    public function isUnseen($element): bool
    {
        return in_array($this->state($element), self::UNSEEN, true);
    }

    public function isReadable($element): bool
    {
        return $this->currentPlayer->seesAll()
            || in_array($this->state($element), self::READABLE, true);
    }

    public function sortByAccess(iterable $elements, bool $freshFirst = false): array
    {
        $elements = is_array($elements) ? array_values($elements) : iterator_to_array($elements, false);

        usort($elements, function ($a, $b) use ($freshFirst) {
            return [$freshFirst && !$this->isFresh($a), $this->rankOf($a)]
                <=> [$freshFirst && !$this->isFresh($b), $this->rankOf($b)];
        });

        return $elements;
    }

    private function rankOf($element): int
    {
        if ($element instanceof Personnage && $element->isDead()) {
            return 5;
        }

        if ($element instanceof Personnage && $element->isMissing()) {
            return 4;
        }

        $state = $this->state($element);

        if ($state === self::PUBLIC) {
            return 0;
        }

        if (in_array($state, self::READABLE, true)) {
            return 1;
        }

        return in_array($state, self::UNSEEN, true) ? 3 : 2;
    }

    public function isListed($element, bool $revealSession = false): bool
    {
        return $this->currentPlayer->seesAll() || $this->state($element, $revealSession) !== self::HIDDEN;
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
        $personal = $this->unlockedAt($element);

        if ($personal !== null) {
            return $personal;
        }

        return $this->publishedAt($element);
    }

    public function isFresh($element): bool
    {
        if ($this->publishedAt($element) === null || !in_array($this->state($element), self::READABLE, true)) {
            return false;
        }

        $date = $this->discoveredAt($element);

        return $date !== null
            && $date <= new \DateTime()
            && $date >= new \DateTime('-' . self::FRESHNESS_DAYS . ' days');
    }

    private function publishedAt($element): ?\DateTimeInterface
    {
        return is_object($element) && method_exists($element, 'getPublishedAt') ? $element->getPublishedAt() : null;
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

    private function isIndividualSecret($element): bool
    {
        return $this->accessOf($element) === Access::SECRET && $element->isManualUnlock();
    }

    private function isPublished($element): bool
    {
        if (!is_object($element) || !method_exists($element, 'getPublishedAt')) {
            return true;
        }

        $publishedAt = $element->getPublishedAt();

        return $publishedAt === null || $publishedAt <= new \DateTime();
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
        $character = $this->currentPlayer->navigatingCharacter();

        if ($character === null) {
            return $this->owned;
        }

        foreach ($this->possessionsOf($character) as $element) {
            $key = EntityRegistry::keyOf($element);

            if ($key !== null) {
                $this->owned[$key][$element->getId()] = true;
            }
        }

        return $this->owned;
    }

    private function possessionsOf(Personnage $character): array
    {
        $possessions = [$character, $character->getEcole(), $character->getClan()];
        $sheet = $character->getFichePersonnage();

        if ($sheet === null) {
            return $possessions;
        }

        $skills = [$sheet->getCompCombatActuelle()];
        for ($i = 1; $i <= 20; $i++) {
            $skills[] = $sheet->{'getCompetence' . $i}();
        }

        return array_merge(
            $possessions,
            [$sheet->getArmeActuelle(), $sheet->getArmure()],
            [$sheet->getAvantage1(), $sheet->getAvantage2(), $sheet->getDesavantage1(), $sheet->getDesavantage2()],
            $skills,
            $sheet->getInventoryItems()->toArray(),
            $sheet->getKnownSpells()->toArray()
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
