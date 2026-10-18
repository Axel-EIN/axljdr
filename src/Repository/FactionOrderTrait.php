<?php

namespace App\Repository;

use App\Entity\FactionType;
use Doctrine\ORM\QueryBuilder;

trait FactionOrderTrait
{
    private function orderByFaction(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->addSelect('COALESCE(c.type, ' . FactionType::RANG_SANS_FACTION . ') AS HIDDEN rang_faction')
            ->addOrderBy('rang_faction', 'ASC')
            ->addOrderBy('c.nom', 'ASC');
    }
}
