<?php

namespace App\Repository;

use App\Entity\Avantage;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Entity\Status;
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

    public function findByAvantage(Avantage $avantage): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.personnage', 'p')
            ->where('f.avantage1 = :avantage OR f.avantage2 = :avantage OR f.desavantage1 = :avantage OR f.desavantage2 = :avantage')
            ->setParameter('avantage', $avantage)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPlacedForLivingPlayers(?Personnage $personnage = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.personnage', 'p')
            ->addSelect('p')
            ->andWhere('f.locX IS NOT NULL')
            ->andWhere('f.locY IS NOT NULL')
            ->andWhere('p.estPj = true')
            ->andWhere('p.status = :vivant')
            ->setParameter('vivant', Status::ALIVE);

        if ($personnage !== null) {
            $qb->andWhere('p = :personnage')->setParameter('personnage', $personnage);
        }

        return $qb->getQuery()->getResult();
    }
}
