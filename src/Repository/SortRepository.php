<?php

namespace App\Repository;

use App\Entity\Sort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sort>
 *
 * @method Sort|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sort|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sort[]    findAll()
 * @method Sort[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sort::class);
    }

    public function findAll()
    {
        return $this->findAllSorted();
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('s')
            ->addOrderBy('s.anneau', 'ASC')
            ->addOrderBy('s.niveau', 'ASC')
            ->addOrderBy('s.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllCategorie($categorie)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->addOrderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countSorts() {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Sort $entity, bool $flush = true): void
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
    public function remove(Sort $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
