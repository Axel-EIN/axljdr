<?php

namespace App\Repository;

use App\Entity\Unlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Unlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unlock[]    findAll()
 * @method Unlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unlock::class);
    }

    public function mapForCharacters(array $characterIds, ?bool $byMeeting = null): array
    {
        if (empty($characterIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('u')
            ->select('u.entity AS entity', 'u.elementId AS elementId', 'u.unlockedAt AS unlockedAt')
            ->andWhere('u.character IN (:ids)')
            ->setParameter('ids', $characterIds);

        if ($byMeeting !== null) {
            $qb->andWhere('u.byMeeting = :parRencontre')->setParameter('parRencontre', $byMeeting);
        }

        $lignes = $qb->getQuery()->getArrayResult();

        $map = [];
        foreach ($lignes as $ligne) {
            $connue = $map[$ligne['entity']][$ligne['elementId']] ?? null;
            if ($connue === null || $ligne['unlockedAt'] < $connue) {
                $map[$ligne['entity']][$ligne['elementId']] = $ligne['unlockedAt'];
            }
        }

        return $map;
    }

    public function findForElement(string $entity, int $elementId): array
    {
        return $this->findBy(['entity' => $entity, 'elementId' => $elementId]);
    }

    public function deleteMeetings(string $entity, int $elementId): void
    {
        $this->createQueryBuilder('u')
            ->delete()
            ->andWhere('u.entity = :entity')
            ->andWhere('u.elementId = :elementId')
            ->andWhere('u.byMeeting = true')
            ->setParameter('entity', $entity)
            ->setParameter('elementId', $elementId)
            ->getQuery()
            ->execute();
    }

    public function deleteForElement(string $entity, int $elementId): void
    {
        $this->createQueryBuilder('u')
            ->delete()
            ->andWhere('u.entity = :entity')
            ->andWhere('u.elementId = :elementId')
            ->setParameter('entity', $entity)
            ->setParameter('elementId', $elementId)
            ->getQuery()
            ->execute();
    }
}
