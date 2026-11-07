<?php

namespace App\Repository;

use App\Entity\Personnage;
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

    public function mapForCharacter(?Personnage $character): array
    {
        if ($character === null) {
            return [];
        }

        $rows = $this->createQueryBuilder('u')
            ->select('u.entity AS entity', 'u.elementId AS elementId', 'u.unlockedAt AS unlockedAt')
            ->andWhere('u.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $known = $map[$row['entity']][$row['elementId']] ?? null;
            if ($known === null || $row['unlockedAt'] < $known) {
                $map[$row['entity']][$row['elementId']] = $row['unlockedAt'];
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
