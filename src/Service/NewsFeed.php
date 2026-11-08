<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Episode;
use App\Entity\Personnage;
use App\Repository\EpisodeRepository;
use App\Repository\ParticipationRepository;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class NewsFeed
{
    private $entityManager;
    private $currentPlayer;
    private $visibility;
    private $episodeRepository;
    private $participationRepository;
    private $characterRepository;
    private $xpRanker;

    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentPlayer $currentPlayer,
        Visibility $visibility,
        EpisodeRepository $episodeRepository,
        ParticipationRepository $participationRepository,
        PersonnageRepository $characterRepository,
        XpRanker $xpRanker
    ) {
        $this->entityManager = $entityManager;
        $this->currentPlayer = $currentPlayer;
        $this->visibility = $visibility;
        $this->episodeRepository = $episodeRepository;
        $this->participationRepository = $participationRepository;
        $this->characterRepository = $characterRepository;
        $this->xpRanker = $xpRanker;
    }

    public function generalRanking(): array
    {
        $ranking = [];

        foreach ($this->characterRepository->findRankable() as $character) {
            if ($character->isDead() || $character->isMissing()) {
                continue;
            }

            $ranking[] = [
                'id' => $character->getId(),
                'firstName' => $character->getPrenom(),
                'icon' => $character->getIcone(),
                'player' => $character->getJoueur(),
                'xp' => $this->xpRanker->total($character),
            ];
        }

        usort($ranking, function ($a, $b) {
            return [$b['xp'], $a['firstName']] <=> [$a['xp'], $b['firstName']];
        });

        return $ranking;
    }

    public function lastEpisodes(int $limit): array
    {
        return $this->episodeRepository->findLastPublished($limit);
    }

    public function episodeLocations(Episode $episode): array
    {
        $locations = [];

        foreach ($episode->getScenes() as $scene) {
            if ($scene->getLieu() !== null) {
                $locations[$scene->getLieu()->getId()] = $scene->getLieu();
            }
        }

        return array_values($locations);
    }

    public function episodeCharacters(Episode $episode): array
    {
        $characters = [];

        foreach ($episode->getScenes() as $scene) {
            foreach ($scene->getParticipations() as $participation) {
                if (!$participation->getEstPj()) {
                    $characters[$participation->getPersonnage()->getId()] = $participation->getPersonnage();
                }
            }
        }

        return array_values($characters);
    }

    public function episodeObjects(Episode $episode): array
    {
        $objects = [];

        foreach ($episode->getScenes() as $scene) {
            foreach ($scene->getFoundObjects() as $object) {
                $objects[$object->getId()] = $object;
            }
        }

        return array_values($objects);
    }

    public function lastParticipations(int $limit): array
    {
        return $this->participationRepository->findLastByEpisode($this->characterIds(), $limit);
    }

    public function latest(string $key, int $limit, array $criteria = []): array
    {
        $class = EntityRegistry::className($key);

        if ($class === null) {
            return [];
        }

        $filter = '';
        foreach ($criteria as $field => $value) {
            $filter .= ' AND e.' . $field . (is_array($value) ? ' IN (:' . $field . ')' : ' = :' . $field);
        }

        $elements = array_merge(
            $this->latestPublished($class, 'e.access = :access' . $filter, ['access' => Access::PUBLIC] + $criteria, $limit),
            $this->visibility->listedOnly($this->latestPublished(
                $class,
                'e.access <> :access AND e.publishedAt >= :freshSince' . $filter,
                ['access' => Access::PUBLIC, 'freshSince' => $this->visibility->freshSince()] + $criteria
            ))
        );

        return array_slice($this->newestFirst($elements), 0, $limit);
    }

    private function latestPublished(string $class, string $condition, array $parameters, ?int $limit = null): array
    {
        $qb = $this->published($class)
            ->andWhere($condition)
            ->orderBy('e.publishedAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit);

        foreach ($parameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        return $qb->getQuery()->getResult();
    }

    public function freshPlayersThenOthers(int $limit): array
    {
        $players = array_filter(
            $this->latest('character', $limit, ['estPj' => true]),
            fn ($character) => $this->visibility->isFresh($character)
        );
        $others = $this->visibility->listedOnly(
            $this->latestPublished(Personnage::class, 'e.estPj = :estPj', ['estPj' => false])
        );

        return array_slice(array_merge($players, $others), 0, $limit);
    }

    public function latestAmong(array $keys, int $limit, array $criteria = []): array
    {
        $elements = [];
        foreach ($keys as $key) {
            foreach ($this->latest($key, $limit, $criteria) as $element) {
                $elements[] = $element;
            }
        }

        return array_slice($this->newestFirst($elements), 0, $limit);
    }

    private function newestFirst(array $elements): array
    {
        usort($elements, function ($a, $b) {
            return [$b->getPublishedAt(), $b->getId()] <=> [$a->getPublishedAt(), $a->getId()];
        });

        return $elements;
    }

    public function lastDiscovered(string $key, int $limit): array
    {
        $class = EntityRegistry::className($key);
        $ids = $this->visibility->unlockedIds($key);

        if ($class === null || $ids === []) {
            return [];
        }

        $byDate = function ($a, $b) {
            return $this->visibility->unlockedAt($b) <=> $this->visibility->unlockedAt($a);
        };

        $references = array_map(function ($id) use ($class) {
            return $this->entityManager->getReference($class, $id);
        }, $ids);
        usort($references, $byDate);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($class, 'e')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', array_map(function ($reference) {
                return $reference->getId();
            }, array_slice($references, 0, $limit)));
        $elements = $this->joinSheet($qb, $class)->getQuery()->getResult();
        usort($elements, $byDate);

        return $elements;
    }

    private function characterIds(): array
    {
        return $this->currentPlayer->characterIds();
    }

    private function published(string $class): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($class, 'e')
            ->andWhere('e.publishedAt <= :now')
            ->setParameter('now', new \DateTime());

        return $this->joinSheet($qb, $class);
    }

    private function joinSheet(QueryBuilder $qb, string $class): QueryBuilder
    {
        return $class === Personnage::class ? $qb->leftJoin('e.fichePersonnage', 'sheet')->addSelect('sheet') : $qb;
    }
}
