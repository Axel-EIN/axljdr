<?php

namespace App\Repository;

use App\Entity\FichePersonnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FichePersonnage|null find($id, $lockMode = null, $lockVersion = null)
 * @method FichePersonnage|null findOneBy(array $criteria, array $orderBy = null)
 * @method FichePersonnage[]    findAll()
 * @method FichePersonnage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichePersonnageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichePersonnage::class);
    }

    public function countFiches() {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
