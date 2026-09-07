<?php

namespace App\Repository;

use App\Entity\Development;
use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Development|null find($id, $lockMode = null, $lockVersion = null)
 * @method Development|null findOneBy(array $criteria, array $orderBy = null)
 * @method Development[]    findAll()
 * @method Development[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DevelopmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Development::class);
    }

    /** @return Development[] */
    public function findByPersonnage(Personnage $personnage): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.participation', 'part')
            ->join('part.scene', 's')
            ->join('s.episodeParent', 'e')
            ->join('e.chapitreParent', 'c')
            ->join('c.saisonParent', 'sai')
            ->andWhere('part.personnage = :personnage')
            ->setParameter('personnage', $personnage)
            ->addOrderBy('sai.numero', 'ASC')
            ->addOrderBy('c.numero', 'ASC')
            ->addOrderBy('e.numero', 'ASC')
            ->addOrderBy('s.numero', 'ASC')
            ->addOrderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
