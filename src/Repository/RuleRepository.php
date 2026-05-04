<?php

namespace App\Repository;

use App\Entity\Rule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rule>
 *
 * @method Rule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rule[]    findAll()
 * @method Rule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rule::class);
    }

    public function findBases()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.base = true')
            ->addOrderBy('r.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAnnexes()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.base = false')
            ->addOrderBy('r.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function FindOthersSameType($id, $type)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id != :value_id')
            ->andWhere('r.base = :value_type')
            ->setParameter('value_id', $id)
            ->setParameter('value_type', $type)
            ->addOrderBy('r.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countRules() {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Rule $entity, bool $flush = true): void
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
    public function remove(Rule $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
