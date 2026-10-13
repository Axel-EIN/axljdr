<?php
namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Repository\ParticipationRepository;

class Localisateur
{
    private $participationRepository;
    private $visibility;
    private $parcours;

    public function __construct(ParticipationRepository $participationRepository, Visibility $visibility)
    {
        $this->participationRepository = $participationRepository;
        $this->visibility = $visibility;
    }

    public function positions(): array
    {
        $positions = [];
        foreach ($this->parcours() as $parcours) {
            $positions[$parcours['lieux'][0]->getId()][] = $parcours['personnage'];
        }

        return $positions;
    }

    public function chemin(?Personnage $personnage, int $longueur = 5): array
    {
        if ($personnage === null) {
            return [];
        }

        $lieux = $this->parcours()[$personnage->getId()]['lieux'] ?? [];

        return array_slice($lieux, 0, $longueur);
    }

    public function position(?Personnage $personnage): ?Lieu
    {
        return $this->chemin($personnage, 1)[0] ?? null;
    }

    private function parcours(): array
    {
        if ($this->parcours !== null) {
            return $this->parcours;
        }

        $this->parcours = [];
        foreach ($this->participationRepository->findPlacedForLivingPlayers() as $participation) {
            $personnage = $participation->getPersonnage();
            $scene = $participation->getScene();

            if (!$this->visibility->isReadable($scene->getEpisodeParent()) || !$this->visibility->isListed($personnage)) {
                continue;
            }

            $id = $personnage->getId();
            $lieux = $this->parcours[$id]['lieux'] ?? [];
            $dernier = end($lieux);

            $this->parcours[$id]['personnage'] = $personnage;
            if ($dernier === false || $dernier->getId() !== $scene->getLieu()->getId()) {
                $this->parcours[$id]['lieux'][] = $scene->getLieu();
            }
        }

        return $this->parcours;
    }
}
