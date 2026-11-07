<?php

namespace App\Repository;

use App\Entity\Access;
use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function findPrevious($chapitreParentID, $numero) {
        return $this->createQueryBuilder('e')
            ->andWhere('e.chapitreParent = :value_chapitreParent')
            ->andWhere('e.numero = :value_numero')
            ->setParameter('value_chapitreParent', $chapitreParentID)
            ->setParameter('value_numero', $numero-1)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findNext($chapitreParentID, $numero) {
        return $this->createQueryBuilder('e')
            ->andWhere('e.chapitreParent = :value_chapitreParent')
            ->andWhere('e.numero = :value_numero')
            ->setParameter('value_chapitreParent', $chapitreParentID)
            ->setParameter('value_numero', $numero+1)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findLastPublished(int $limit): array
    {
        return self::onlyPublished($this->createQueryBuilder('e'), 'e')
            ->orderBy('e.numeroSaison', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public static function onlyPublished(QueryBuilder $qb, string $alias): QueryBuilder
    {
        return $qb
            ->andWhere($alias . '.access = :publishedAccess')
            ->andWhere($alias . '.publishedAt IS NULL OR ' . $alias . '.publishedAt <= :publishedNow')
            ->setParameter('publishedAccess', Access::PUBLIC)
            ->setParameter('publishedNow', new \DateTime());
    }

    public function nextCampaignNumber(): int
    {
        return 1 + (int) $this->createQueryBuilder('e')
            ->select('MAX(e.numeroSaison)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEpisodes() {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
