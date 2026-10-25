<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Episode;
use App\Repository\ParticipationRepository;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class NewsFeed
{
    private $entityManager;
    private $currentPlayer;
    private $visibility;
    private $participationRepository;
    private $personnageRepository;
    private $classeurXP;

    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentPlayer $currentPlayer,
        Visibility $visibility,
        ParticipationRepository $participationRepository,
        PersonnageRepository $personnageRepository,
        ClasseurXP $classeurXP
    ) {
        $this->entityManager = $entityManager;
        $this->currentPlayer = $currentPlayer;
        $this->visibility = $visibility;
        $this->participationRepository = $participationRepository;
        $this->personnageRepository = $personnageRepository;
        $this->classeurXP = $classeurXP;
    }

    public function generalRanking(): array
    {
        $classement = [];

        foreach ($this->personnageRepository->findRankable() as $personnage) {
            if ($personnage->isDead() || $personnage->isMissing()) {
                continue;
            }

            $classement[] = [
                'id' => $personnage->getId(),
                'prenom' => $personnage->getPrenom(),
                'icone' => $personnage->getIcone(),
                'joueur' => $personnage->getJoueur(),
                'xp' => $this->classeurXP->total($personnage),
                'estMort' => $personnage->isDead() ? 1 : 0,
            ];
        }

        usort($classement, function ($a, $b) {
            return [$b['xp'], $a['prenom']] <=> [$a['xp'], $b['prenom']];
        });

        return $classement;
    }

    public function lastEpisodes(int $limit): array
    {
        return $this->parus(Episode::class, true)
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

    public function latest(string $key, int $limit, array $criteres = []): array
    {
        $class = EntityRegistry::className($key);

        if ($class === null) {
            return [];
        }

        $filtre = '';
        foreach ($criteres as $champ => $valeur) {
            $filtre .= ' AND e.' . $champ . (is_array($valeur) ? ' IN (:' . $champ . ')' : ' = :' . $champ);
        }

        return $this->publies($class, $limit, 'e.access = :palier' . $filtre, ['palier' => Access::PUBLIC] + $criteres);
    }

    private function publies(string $class, int $limit, string $condition, array $parametres): array
    {
        $qb = $this->parus($class, false)
            ->andWhere($condition)
            ->orderBy('e.publishedAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit);

        foreach ($parametres as $nom => $valeur) {
            $qb->setParameter($nom, $valeur);
        }

        return $qb->getQuery()->getResult();
    }

    public function latestAmong(array $keys, int $limit, array $criteres = []): array
    {
        $elements = [];
        foreach ($keys as $key) {
            foreach ($this->latest($key, $limit, $criteres) as $element) {
                $elements[] = $element;
            }
        }

        usort($elements, function ($a, $b) {
            return [$b->getPublishedAt(), $b->getId()] <=> [$a->getPublishedAt(), $a->getId()];
        });

        return array_slice($elements, 0, $limit);
    }

    public function lastDiscovered(string $key, int $limit): array
    {
        $class = EntityRegistry::className($key);
        $ids = $this->visibility->unlockedIds($key);

        if ($class === null || $ids === []) {
            return [];
        }

        $parDate = function ($a, $b) {
            return $this->visibility->unlockedAt($b) <=> $this->visibility->unlockedAt($a);
        };

        $references = array_map(function ($id) use ($class) {
            return $this->entityManager->getReference($class, $id);
        }, $ids);
        usort($references, $parDate);

        $elements = $this->entityManager->getRepository($class)->findBy(['id' => array_map(function ($reference) {
            return $reference->getId();
        }, array_slice($references, 0, $limit))]);
        usort($elements, $parDate);

        return $elements;
    }

    private function characterIds(): array
    {
        return $this->currentPlayer->characterIds();
    }

    private function parus(string $class, bool $memeSansDate): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($class, 'e')
            ->andWhere($memeSansDate ? 'e.publishedAt IS NULL OR e.publishedAt <= :maintenant' : 'e.publishedAt <= :maintenant')
            ->setParameter('maintenant', new \DateTime());
    }
}
