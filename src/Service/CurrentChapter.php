<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Episode;
use App\Entity\Scene;
use App\Repository\EpisodeRepository;
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
    private $lastEpisode = false;
    private $elementIds = [];

    public function __construct(EntityManagerInterface $entityManager, EpisodeRepository $episodeRepository)
    {
        $this->entityManager = $entityManager;
        $this->episodeRepository = $episodeRepository;
    }

    public function lastEpisode(): ?Episode
    {
        if ($this->lastEpisode === false) {
            $this->lastEpisode = $this->episodeRepository->findLastPublished(1)[0] ?? null;
        }

        return $this->lastEpisode;
    }

    public function contains($element): bool
    {
        $key = EntityRegistry::keyOf($element);

        return $key !== null && isset($this->elementIds($key)[$element->getId()]);
    }

    private function elementIds(string $key): array
    {
        if (!isset($this->elementIds[$key])) {
            $this->elementIds[$key] = $this->findElementIds($key);
        }

        return $this->elementIds[$key];
    }

    private function findElementIds(string $key): array
    {
        $chapter = $this->lastEpisode()?->getChapitreParent();
        $source = self::SOURCES[$key] ?? null;

        if ($chapter === null || $source === null) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT ' . $source['id'] . ' AS id')
            ->from(Scene::class, 's')
            ->join('s.episodeParent', 'e')
            ->andWhere('e.chapitreParent = :chapter')
            ->andWhere('e.access = :access')
            ->andWhere('e.publishedAt IS NULL OR e.publishedAt <= :now')
            ->setParameter('chapter', $chapter)
            ->setParameter('access', Access::PUBLIC)
            ->setParameter('now', new \DateTime());

        if ($source['join'] !== null) {
            $qb->join($source['join'], 'element');
        }

        return array_fill_keys(array_column($qb->getQuery()->getScalarResult(), 'id'), true);
    }
}
