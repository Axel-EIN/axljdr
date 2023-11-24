<?php

namespace App\Repository;

use App\Entity\Lore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lore>
 *
 * @method Lore|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lore|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lore[]    findAll()
 * @method Lore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lore::class);
    }

    public function findAll() {
        return $this->findAllOrdered();
    }

    public function findAllOrdered() {
        return $this->createQueryBuilder('l')
        ->addOrderBy('l.numero', 'ASC')
        ->getQuery()
        ->getResult();
    }

    public function countLores() {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllExceptOne($id)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.id != :value_id')
            ->setParameter('value_id', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Lore $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Lore $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
