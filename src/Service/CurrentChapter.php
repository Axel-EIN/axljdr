<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Scene;
use App\Repository\EpisodeRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;

class CurrentChapter
{
    private const SOURCES = [
        'character' => ['join' => 's.participations', 'id' => 'IDENTITY(element.personnage)'],
        'location' => ['join' => null, 'id' => 'IDENTITY(s.lieu)'],
        'object' => ['join' => 's.foundObjects', 'id' => 'element.id'],
    ];

    private $entityManager;
    private $episodeRepository;
    private $participationRepository;
    private $currentPlayer;
    private $lastEpisode = false;
    private $chapterIds;
    private $elementIds = [];
    private $lastEpisodeIds = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        EpisodeRepository $episodeRepository,
        ParticipationRepository $participationRepository,
        CurrentPlayer $currentPlayer
    ) {
        $this->entityManager = $entityManager;
        $this->episodeRepository = $episodeRepository;
        $this->participationRepository = $participationRepository;
        $this->currentPlayer = $currentPlayer;
    }

    public function lastEpisode(): ?Episode
    {
        if ($this->lastEpisode === false) {
            $this->lastEpisode = $this->episodeRepository->findLastPublished(1)[0] ?? null;
        }

        return $this->lastEpisode;
    }

    public function isRevealed($element): bool
    {
        $key = EntityRegistry::keyOf($element);

        return $key !== null && isset($this->elementIds($key)[$element->getId()]);
    }

    private function chapterIds(): array
    {
        if ($this->chapterIds === null) {
            $character = $this->currentPlayer->navigatingCharacter();

            $this->chapterIds = array_values(array_unique(array_filter([
                $this->lastEpisode()?->getChapitreParent()?->getId(),
                $character === null ? null : $this->participationRepository->findLastChapterId($character),
            ])));
        }

        return $this->chapterIds;
    }

    private function elementIds(string $key): array
    {
        if (!isset($this->elementIds[$key])) {
            $this->elementIds[$key] = $this->findElementIds($key);
        }

        return $this->elementIds[$key];
    }

    public function lastEpisodeIds(string $key): array
    {
        if (!isset($this->lastEpisodeIds[$key])) {
            $episode = $this->lastEpisode();

            $this->lastEpisodeIds[$key] = $episode === null ? [] : array_keys($this->sceneElementIds($key, 'e = :scope', $episode));
        }

        return $this->lastEpisodeIds[$key];
    }

    private function findElementIds(string $key): array
    {
        $chapterIds = $this->chapterIds();

        return $chapterIds === [] ? [] : $this->sceneElementIds($key, 'e.chapitreParent IN (:scope)', $chapterIds);
    }

    private function sceneElementIds(string $key, string $condition, $scope): array
    {
        $source = self::SOURCES[$key] ?? null;

        if ($source === null) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT ' . $source['id'] . ' AS id')
            ->from(Scene::class, 's')
            ->join('s.episodeParent', 'e')
            ->andWhere($condition)
            ->setParameter('scope', $scope);
        EpisodeRepository::onlyPublished($qb, 'e');

        if ($source['join'] !== null) {
            $qb->join($source['join'], 'element');
        }

        return array_fill_keys(array_filter(array_column($qb->getQuery()->getScalarResult(), 'id')), true);
    }
}
