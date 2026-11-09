<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Personnage;
use App\Entity\Status;
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

    private const FRESHNESS_DAYS = 7;

    private const RANKS = [
        self::PUBLIC => 0,
        self::UNLOCKED => 1,
        self::DISCOVERED => 1,
        self::COMMON_DISCOVERED => 1,
        self::LOCKED => 3,
        self::HIDDEN => 4,
        self::SECRET => 4,
        self::COMMON => 4,
    ];

    private const CHAPTER_REVEALED_RANK = 2;

    private const CHAPTER_REVEALED = [self::SECRET, self::COMMON];

    private const INACTIVE_RANKS = [
        Status::MISSING => 5,
        Status::DEAD => 6,
    ];

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
    private $currentChapter;
    private $unlocks;
    private $owned;

    public function __construct(CurrentPlayer $currentPlayer, UnlockRepository $unlockRepository, CurrentChapter $currentChapter)
    {
        $this->currentPlayer = $currentPlayer;
        $this->unlockRepository = $unlockRepository;
        $this->currentChapter = $currentChapter;
    }

    public function state($element, bool $revealSession = false): string
    {
        $access = $this->accessOf($element);

        if ($this->isOwned($element) || $this->isUnlocked($element)) {
            return Access::needsUnlock($access) ? self::REVEALED[$access] : self::PUBLIC;
        }

        if ($this->currentPlayer->seesAll()) {
            return self::LEVELS[$access];
        }

        if ($access === Access::PUBLIC) {
            return $this->isPublished($element) ? self::PUBLIC : self::HIDDEN;
        }

        if (($revealSession || $this->currentChapter->isRevealed($element)) && !$this->isIndividuallyHidden($element)) {
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
        if ($element instanceof Personnage && isset(self::INACTIVE_RANKS[$element->getStatus()])) {
            return self::INACTIVE_RANKS[$element->getStatus()];
        }

        $state = $this->state($element);

        if (!$this->currentPlayer->seesAll() && in_array($state, self::CHAPTER_REVEALED, true)) {
            return self::CHAPTER_REVEALED_RANK;
        }

        return self::RANKS[$state];
    }

    public function isListed($element, bool $revealSession = false): bool
    {
        return $this->currentPlayer->seesAll() || $this->state($element, $revealSession) !== self::HIDDEN;
    }

    public function listedOnly(iterable $elements, bool $revealSession = false): array
    {
        $elements = is_array($elements) ? $elements : iterator_to_array($elements, false);

        return array_values(array_filter($elements, function ($element) use ($revealSession) {
            return $this->isListed($element, $revealSession);
        }));
    }

    public function isFresh($element): bool
    {
        $publishedAt = $this->publishedAt($element);

        return $publishedAt !== null
            && $publishedAt <= new \DateTime()
            && $publishedAt >= $this->freshSince()
            && in_array($this->state($element), self::READABLE, true);
    }

    public function freshSince(): \DateTimeInterface
    {
        $since = new \DateTime('-' . self::FRESHNESS_DAYS . ' days');
        $episodeDate = $this->currentChapter->lastEpisode()?->getPublishedAt();

        if ($episodeDate === null) {
            return $since;
        }

        return min($since, \DateTime::createFromInterface($episodeDate)->setTime(0, 0)->modify('-' . self::FRESHNESS_DAYS . ' days'));
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

    private function isIndividuallyHidden($element): bool
    {
        return Access::isHidden($this->accessOf($element)) && $element->isManualUnlock();
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

        return $this->unlocks = $this->unlockRepository->mapForCharacter($this->currentPlayer->navigatingCharacter());
    }
}
