<?php

namespace App\Repository;

use App\Entity\Avantage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avantage>
 *
 * @method Avantage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avantage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avantage[]    findAll()
 * @method Avantage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvantageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avantage::class);
    }

    public function countAvantages() {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Avantage $entity, bool $flush = true): void
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
    public function remove(Avantage $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
