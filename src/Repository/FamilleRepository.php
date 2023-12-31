<?php

namespace App\Repository;

use App\Entity\Famille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Famille>
 *
 * @method Famille|null find($id, $lockMode = null, $lockVersion = null)
 * @method Famille|null findOneBy(array $criteria, array $orderBy = null)
 * @method Famille[]    findAll()
 * @method Famille[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Famille::class);
    }

    public function findAll()
    {
        return $this->findAllSorted();
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('e')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countFamilles() {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Famille $entity, bool $flush = true): void
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
    public function remove(Famille $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
