<?php

namespace App\Repository;

use App\Entity\Library;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Library>
 *
 * @method Library|null find($id, $lockMode = null, $lockVersion = null)
 * @method Library|null findOneBy(array $criteria, array $orderBy = null)
 * @method Library[]    findAll()
 * @method Library[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LibraryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Library::class);
    }

    public function findBases()
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.base = true')
            ->addOrderBy('l.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAnnexes()
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.base = false')
            ->addOrderBy('l.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function FindOthersSameType($id, $type)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.id != :value_id')
            ->andWhere('l.base = :value_type')
            ->setParameter('value_id', $id)
            ->setParameter('value_type', $type)
            ->addOrderBy('l.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countLibraries() {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Library $entity, bool $flush = true): void
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
    public function remove(Library $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
