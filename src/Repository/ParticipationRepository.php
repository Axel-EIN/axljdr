<?php

namespace App\Repository;

use App\Entity\Lieu;
use App\Entity\Participation;
use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Participation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participation[]    findAll()
 * @method Participation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    public function findLastByEpisode(array $characterIds, int $limit): array
    {
        if (empty($characterIds)) {
            return [];
        }

        $dernieres = $this->createQueryBuilder('p')
            ->select('MAX(p.id) AS derniere', 'e.numeroSaison AS numero')
            ->join('p.scene', 's')
            ->join('s.episodeParent', 'e')
            ->andWhere('p.personnage IN (:ids)')
            ->andWhere('p.estPj = true')
            ->setParameter('ids', $characterIds)
            ->groupBy('e.id')
            ->addGroupBy('e.numeroSaison')
            ->orderBy('e.numeroSaison', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();

        $ids = array_map('intval', array_column($dernieres, 'derniere'));

        if (empty($ids)) {
            return [];
        }

        $participations = $this->createQueryBuilder('p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        usort($participations, function ($a, $b) use ($ids) {
            return array_search($a->getId(), $ids) <=> array_search($b->getId(), $ids);
        });

        return $participations;
    }

    public function findPlayersWhoMet(Personnage $personnage): array
    {
        $participations = $this->createQueryBuilder('moi')
            ->join('moi.personnage', 'joueur')
            ->addSelect('joueur')
            ->join(Participation::class, 'autre', 'WITH', 'autre.scene = moi.scene')
            ->andWhere('autre.personnage = :element')
            ->andWhere('moi.personnage != :element')
            ->andWhere('moi.estPj = true')
            ->setParameter('element', $personnage)
            ->getQuery()
            ->getResult();

        return $this->dedoublonner($participations);
    }

    public function findPlayersWhoVisited(Lieu $lieu): array
    {
        $participations = $this->createQueryBuilder('p')
            ->join('p.personnage', 'joueur')
            ->addSelect('joueur')
            ->join('p.scene', 's')
            ->andWhere('s.lieu = :lieu')
            ->andWhere('p.estPj = true')
            ->setParameter('lieu', $lieu)
            ->getQuery()
            ->getResult();

        return $this->dedoublonner($participations);
    }

    private function dedoublonner(array $participations): array
    {
        $personnages = [];
        foreach ($participations as $participation) {
            $personnages[$participation->getPersonnage()->getId()] = $participation->getPersonnage();
        }

        return array_values($personnages);
    }

    public function findEncountersForEpisodes(array $episodeIds, array $characterIds): array
    {
        if (empty($episodeIds) || empty($characterIds)) {
            return [];
        }

        $participations = $this->createQueryBuilder('p')
            ->join('p.scene', 's')
            ->join('p.personnage', 'personnage')
            ->addSelect('personnage')
            ->andWhere('s.episodeParent IN (:episodes)')
            ->andWhere('personnage.id NOT IN (:miens)')
            ->setParameter('episodes', $episodeIds)
            ->setParameter('miens', $characterIds)
            ->orderBy('personnage.estPj', 'DESC')
            ->addOrderBy('personnage.prenom', 'ASC')
            ->getQuery()
            ->getResult();

        $rencontres = [];
        foreach ($participations as $participation) {
            $rencontres[$participation->getPersonnage()->getId()] = $participation->getPersonnage();
        }

        return array_values($rencontres);
    }
}
