<?php

namespace App\Repository;

use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Personnage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personnage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personnage[]    findAll()
 * @method Personnage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnageRepository extends ServiceEntityRepository
{
    use FactionOrderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnage::class);
    }

    public function findAllPJsSorted(?int $saisonId = null, ?int $chapitreId = null, ?int $episodeId = null)
    {
        return $this->findSorted(true, $saisonId, $chapitreId, $episodeId);
    }

    public function findAllPNJsSorted(?int $saisonId = null, ?int $chapitreId = null, ?int $episodeId = null)
    {
        return $this->findSorted(false, $saisonId, $chapitreId, $episodeId);
    }

    private function findSorted(bool $estPj, ?int $saisonId, ?int $chapitreId, ?int $episodeId): array
    {
        $qb = $this->createListQueryBuilder()
            ->andWhere('p.estPj = :estPj')
            ->setParameter('estPj', $estPj)
            ->leftJoin('p.clan', 'c');

        $this->applySaisonChapitreEpisodeFilter($qb, $saisonId, $chapitreId, $episodeId);

        return $this->orderByFaction($qb->addOrderBy('p.status', 'ASC'))
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function applySaisonChapitreEpisodeFilter($qb, ?int $saisonId, ?int $chapitreId, ?int $episodeId): void
    {
        if ($episodeId === null && $chapitreId === null && $saisonId === null) {
            return;
        }

        $sub = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(part.personnage)')
            ->from(\App\Entity\Participation::class, 'part')
            ->innerJoin('part.scene', 'sc')
            ->innerJoin('sc.episodeParent', 'ep')
            ->innerJoin('ep.chapitreParent', 'ch');

        if ($episodeId !== null) {
            $sub->andWhere('ep.id = :episodeId');
            $qb->setParameter('episodeId', $episodeId);
        } elseif ($chapitreId !== null) {
            $sub->andWhere('ch.id = :chapitreId');
            $qb->setParameter('chapitreId', $chapitreId);
        } else {
            $sub->andWhere('ch.saisonParent = :saisonId');
            $qb->setParameter('saisonId', $saisonId);
        }

        $qb->andWhere($qb->expr()->in('p.id', $sub->getDQL()));
    }

    public function findAllPJsExceptOne($id)
    {
        return $this->findExceptOne(true, $id);
    }

    public function findAllPNJsExceptOne($id)
    {
        return $this->findExceptOne(false, $id);
    }

    private function findExceptOne(bool $estPj, $id): array
    {
        return $this->createListQueryBuilder()
            ->andWhere('p.estPj = :estPj')
            ->andWhere('p.id != :value_id')
            ->setParameter('estPj', $estPj)
            ->setParameter('value_id', $id)
            ->getQuery()
            ->getResult();
    }

    public function createPJsByNameQueryBuilder(): QueryBuilder
    {
        return $this->createListQueryBuilder()
            ->andWhere('p.estPj = true')
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC');
    }

    public function countPersonnages() {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRankable(): array
    {
        return $this->createListQueryBuilder()
            ->andWhere('p.estPj = true')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.fichePersonnage', 'fiche')
            ->addSelect('fiche');
    }
}
