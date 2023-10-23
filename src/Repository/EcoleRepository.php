<?php

namespace App\Repository;

use App\Entity\Ecole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ecole|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ecole|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ecole[]    findAll()
 * @method Ecole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ecole::class);
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.clan', 'c')
            ->addOrderBy('c.estMajeur', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByClasseSorted($id)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.classe = :value_id')
            ->setParameter('value_id', $id)
            ->leftJoin('e.clan', 'c')
            ->addOrderBy('c.estMajeur', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByClanExceptOne($clanId, $ecoleId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.clan = :value_clanId')
            ->setParameter('value_clanId', $clanId)
            ->andWhere('e.id != :value_ecoleId')
            ->setParameter('value_ecoleId', $ecoleId)
            ->getQuery()
            ->getResult();
    }

    public function countEcoles() {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

}
