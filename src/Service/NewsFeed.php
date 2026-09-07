<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Episode;
use App\Repository\ParticipationRepository;
use App\Repository\PersonnageRepository;
use App\Repository\UnlockRepository;
use Doctrine\ORM\EntityManagerInterface;

class NewsFeed
{
    private $entityManager;
    private $currentPlayer;
    private $unlockRepository;
    private $participationRepository;
    private $personnageRepository;
    private $classeurXP;
    private $unlocks;

    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentPlayer $currentPlayer,
        UnlockRepository $unlockRepository,
        ParticipationRepository $participationRepository,
        PersonnageRepository $personnageRepository,
        ClasseurXP $classeurXP
    ) {
        $this->entityManager = $entityManager;
        $this->currentPlayer = $currentPlayer;
        $this->unlockRepository = $unlockRepository;
        $this->participationRepository = $participationRepository;
        $this->personnageRepository = $personnageRepository;
        $this->classeurXP = $classeurXP;
    }

    public function generalRanking(): array
    {
        $classement = [];

        foreach ($this->personnageRepository->findRankable() as $personnage) {
            $classement[] = [
                'id' => $personnage->getId(),
                'prenom' => $personnage->getPrenom(),
                'icone' => $personnage->getIcone(),
                'joueur' => $personnage->getJoueur(),
                'xp' => $this->classeurXP->total($personnage),
                'estMort' => $personnage->isDead() ? 1 : 0,
                'missing' => $personnage->isMissing(),
            ];
        }

        usort($classement, function ($a, $b) {
            return [$b['xp'], $a['prenom']] <=> [$a['xp'], $b['prenom']];
        });

        return $classement;
    }

    public function lastEpisodes(int $limit): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Episode::class, 'e')
            ->andWhere('e.access = :palier')
            ->setParameter('palier', Access::PUBLIC)
            ->orderBy('e.numeroSaison', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function episodeLocations(Episode $episode): array
    {
        $lieux = [];

        foreach ($episode->getScenes() as $scene) {
            if ($scene->getLieu() !== null) {
                $lieux[$scene->getLieu()->getId()] = $scene->getLieu();
            }
        }

        return array_values($lieux);
    }

    public function episodeCharacters(Episode $episode): array
    {
        $personnages = [];

        foreach ($episode->getScenes() as $scene) {
            foreach ($scene->getParticipations() as $participation) {
                if (!$participation->getEstPj()) {
                    $personnages[$participation->getPersonnage()->getId()] = $participation->getPersonnage();
                }
            }
        }

        return array_values($personnages);
    }

    public function episodeObjects(Episode $episode): array
    {
        $objets = [];

        foreach ($episode->getScenes() as $scene) {
            foreach ($scene->getFoundObjects() as $objet) {
                $objets[$objet->getId()] = $objet;
            }
        }

        return array_values($objets);
    }

    public function lastParticipations(int $limit): array
    {
        return $this->participationRepository->findLastByEpisode($this->characterIds(), $limit);
    }

    public function encounters(array $participations): array
    {
        $episodes = [];
        foreach ($participations as $participation) {
            $episodes[] = $participation->getScene()->getEpisodeParent()->getId();
        }

        return $this->participationRepository->findEncountersForEpisodes($episodes, $this->characterIds());
    }

    public function latest(string $key, int $limit): array
    {
        $class = EntityRegistry::className($key);

        if ($class === null) {
            return [];
        }

        $elements = $this->publies($class, $limit, 'e.access = :palier', ['palier' => Access::PUBLIC]);
        $rencontres = $this->metDates($key);

        if (!empty($rencontres)) {
            arsort($rencontres);
            $elements = array_merge($elements, $this->publies(
                $class,
                $limit,
                'e.access = :palier AND e.id IN (:croises)',
                ['palier' => Access::AUTO, 'croises' => array_slice(array_keys($rencontres), 0, $limit)]
            ));
        }

        usort($elements, function ($a, $b) use ($rencontres) {
            return $this->decouverteLe($b, $rencontres) <=> $this->decouverteLe($a, $rencontres);
        });

        return array_slice($elements, 0, $limit);
    }

    private function publies(string $class, int $limit, string $condition, array $parametres): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($class, 'e')
            ->andWhere('e.publishedAt IS NOT NULL AND e.publishedAt <= :maintenant')
            ->andWhere($condition)
            ->setParameter('maintenant', new \DateTime())
            ->orderBy('e.publishedAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit);

        foreach ($parametres as $nom => $valeur) {
            $qb->setParameter($nom, $valeur);
        }

        return $qb->getQuery()->getResult();
    }

    private function decouverteLe($element, array $rencontres): \DateTimeInterface
    {
        return $rencontres[$element->getId()] ?? $element->getPublishedAt();
    }

    public function latestAmong(array $keys, int $limit): array
    {
        $elements = [];
        foreach ($keys as $key) {
            foreach ($this->latest($key, $limit) as $element) {
                $elements[] = $element;
            }
        }

        usort($elements, function ($a, $b) {
            return [$b->getPublishedAt(), $b->getId()] <=> [$a->getPublishedAt(), $a->getId()];
        });

        return array_slice($elements, 0, $limit);
    }

    public function unlockedByEntity(int $limit): array
    {
        $parEntite = [];

        foreach ($this->unlockedByCharacters($limit * 12) as $element) {
            $cle = EntityRegistry::keyOf($element);

            if ($cle !== null && count($parEntite[$cle] ?? []) < $limit) {
                $parEntite[$cle][] = $element;
            }
        }

        return $parEntite;
    }

    public function unlockedByCharacters(int $limit): array
    {
        $personnages = $this->characterIds();

        if (empty($personnages)) {
            return [];
        }

        $dates = [];
        $elements = [];

        foreach ($this->unlockRepository->mapForCharacters($personnages, false) as $key => $lignes) {
            $class = EntityRegistry::className($key);

            if ($class === null) {
                continue;
            }

            $trouves = $this->entityManager->createQueryBuilder()
                ->select('e')
                ->from($class, 'e')
                ->andWhere('e.id IN (:ids)')
                ->andWhere('e.access <= :locked')
                ->setParameter('ids', array_keys($lignes))
                ->setParameter('locked', Access::LOCKED)
                ->getQuery()
                ->getResult();

            foreach ($trouves as $element) {
                $elements[] = $element;
                $dates[spl_object_id($element)] = $lignes[$element->getId()];
            }
        }

        usort($elements, function ($a, $b) use ($dates) {
            return $dates[spl_object_id($b)] <=> $dates[spl_object_id($a)];
        });

        return array_slice($elements, 0, $limit);
    }

    private function metDates(string $key): array
    {
        if ($this->unlocks === null) {
            $this->unlocks = $this->unlockRepository->mapForCharacters($this->characterIds());
        }

        return $this->unlocks[$key] ?? [];
    }

    private function characterIds(): array
    {
        return $this->currentPlayer->characterIds();
    }
}
