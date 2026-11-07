<?php

namespace App\Repository;

use App\Entity\Lieu;
use App\Entity\Scene;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Scene|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scene|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scene[]    findAll()
 * @method Scene[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SceneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scene::class);
    }

    public function findByLocationWithEpisode(Lieu $location): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.episodeParent', 'e')
            ->join('e.chapitreParent', 'c')
            ->join('c.saisonParent', 'sa')
            ->addSelect('e', 'c', 'sa')
            ->andWhere('s.lieu = :location')
            ->setParameter('location', $location)
            ->getQuery()
            ->getResult();
    }

    public function countScenes() {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
