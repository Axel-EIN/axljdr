<?php

namespace App\Repository;

use App\Entity\Objet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objet>
 *
 * @method Objet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Objet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Objet[]    findAll()
 * @method Objet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objet::class);
    }

    public function countObjets() {
        return $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAll()
    {
        return $this->findAllSorted();
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('o')
            ->addOrderBy('o.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllCategorie($categorie)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->addOrderBy('o.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Objet $entity, bool $flush = true): void
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
    public function remove(Objet $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
