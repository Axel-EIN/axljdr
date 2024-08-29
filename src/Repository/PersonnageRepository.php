<?php

namespace App\Repository;

use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Personnage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personnage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personnage[]    findAll()
 * @method Personnage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnage::class);
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.clan', 'c')
            ->addOrderBy('c.estMajeur', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllPJsSorted()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estPj = :val')
            ->setParameter('val', 1)
            ->leftJoin('p.clan', 'c')
            ->addOrderBy('p.estMort', 'ASC')
            ->addOrderBy('c.estMajeur', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllPNJsSorted()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estPj = :val')
            ->setParameter('val', 0)
            ->leftJoin('p.clan', 'c')
            ->addOrderBy('p.estMort', 'ASC')
            // ->addOrderBy('p.locked', 'ASC')
            ->addOrderBy('c.estMajeur', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllExceptOne($id)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id != :value_id')
            ->setParameter('value_id', $id)
            ->getQuery()
            ->getResult();

    }

    // SQL COUNT CHARACTERS

    public function countPersonnages() {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    

    public function countPJs() {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.estPj = :val')
            ->setParameter('val', 1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPNJs() {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.estPj = :val')
            ->setParameter('val', 0)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ->getSingleScalarResult permet de récupérer un nombre
    // ->getOneOrNullResult permet de récupérer qu'un seul élément ou bien null
}
