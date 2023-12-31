<?php

namespace App\Repository;

use App\Entity\Clan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Clan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clan[]    findAll()
 * @method Clan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clan::class);
    }

    public function findAll()
    {
        return $this->findAllSorted();
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('c')
            ->addOrderBy('c.estMajeur', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllMajeurs()
    {
        return $this->findBy(array("estMajeur" => "1"), array('nom' => 'ASC'));
    }

    public function findAllAutres()
    {
        return $this->findBy(array("estMajeur" => "0"), array('nom' => 'ASC'));
    }

    public function findAllExceptOne($id)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id != :value_id')
            ->setParameter('value_id', $id)
            ->getQuery()
            ->getResult();
    }

    public function countClans() {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
