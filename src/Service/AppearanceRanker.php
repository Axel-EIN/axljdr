<?php

namespace App\Service;

use App\Entity\Personnage;
use App\Entity\Status;
use App\Repository\ParticipationRepository;

class AppearanceRanker
{
    private $participationRepository;
    private $ranks;

    public function __construct(ParticipationRepository $participationRepository)
    {
        $this->participationRepository = $participationRepository;
    }

    public function sort(array $characters): array
    {
        $this->ranks ??= $this->participationRepository->findAppearanceRanks();

        usort($characters, function (Personnage $a, Personnage $b) {
            return [Status::SORT_ORDER[$a->getStatus()], $this->ranks[$a->getId()] ?? PHP_INT_MAX]
                <=> [Status::SORT_ORDER[$b->getStatus()], $this->ranks[$b->getId()] ?? PHP_INT_MAX];
        });

        return $characters;
    }
}
