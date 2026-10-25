<?php
namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Participation;
use App\Entity\Personnage;
use App\Entity\Scene;
use App\Repository\FichePersonnageRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;

class Locator
{
    private $participationRepository;
    private $sheetRepository;
    private $visibility;
    private $entityManager;
    private $locationRepository;
    private $journeys;
    private $wanderers;

    public function __construct(ParticipationRepository $participationRepository, FichePersonnageRepository $sheetRepository, Visibility $visibility, EntityManagerInterface $entityManager, LieuRepository $locationRepository)
    {
        $this->participationRepository = $participationRepository;
        $this->sheetRepository = $sheetRepository;
        $this->visibility = $visibility;
        $this->entityManager = $entityManager;
        $this->locationRepository = $locationRepository;
    }

    public function positions(): array
    {
        $positions = [];
        foreach ($this->players() as $player) {
            if ($player['location'] !== null) {
                $positions[$player['location']->getId()][] = $player['character'];
            }
        }

        return $positions;
    }

    public function players(): array
    {
        $players = [];
        $wanderers = $this->wanderers();
        foreach ($this->journeys() as $id => $journey) {
            if (!isset($wanderers[$id])) {
                $location = $journey['locations'][0];
                $players[] = ['character' => $journey['character'], 'location' => $location, 'locX' => $location->getLocX(), 'locY' => $location->getLocY()];
            }
        }

        foreach ($wanderers as $wanderer) {
            $players[] = ['character' => $wanderer['character'], 'location' => null, 'locX' => $wanderer['locX'], 'locY' => $wanderer['locY']];
        }

        return $players;
    }

    public function wanderers(): array
    {
        if ($this->wanderers !== null) {
            return $this->wanderers;
        }

        $this->wanderers = $this->listWanderers($this->sheetRepository->findPlacedForLivingPlayers());

        return $this->wanderers;
    }

    public function freePosition(?Personnage $character): ?array
    {
        if ($character === null) {
            return null;
        }

        $wanderers = $this->wanderers ?? $this->listWanderers($this->sheetRepository->findPlacedForLivingPlayers($character));

        return $wanderers[$character->getId()] ?? null;
    }

    public function trail(?Personnage $character, int $length = 6): array
    {
        if ($character === null) {
            return [];
        }

        $steps = array_map(function (Lieu $location) {
            return ['locX' => $location->getLocX(), 'locY' => $location->getLocY()];
        }, $this->journeys()[$character->getId()]['locations'] ?? []);
        $free = $this->freePosition($character);

        if ($free !== null) {
            array_unshift($steps, ['locX' => $free['locX'], 'locY' => $free['locY']]);
        }

        return array_slice($steps, 0, $length);
    }

    public function clearFreePositions(Scene $scene, ?array $characters = null): array
    {
        $location = $scene->getLieu();
        if ($location === null || !$location->isPlaced()) {
            return [];
        }

        if ($characters === null) {
            $characters = array_map(function (Participation $participation) {
                return $participation->getPersonnage();
            }, $this->participationRepository->findBy(['scene' => $scene]));
        }

        $cleared = [];
        foreach ($characters as $character) {
            $sheet = $character->getFichePersonnage();
            if ($sheet === null || !$sheet->hasPositionLibre()) {
                continue;
            }

            $last = $this->participationRepository->findLastForCharacter($character);
            if ($last !== null && $last->getScene()->getId() === $scene->getId()) {
                $sheet->setLocX(null)->setLocY(null);
                $cleared[] = $character;
            }
        }

        if ($cleared !== []) {
            $this->entityManager->flush();
            $this->wanderers = null;
        }

        return $cleared;
    }

    public function position(?Personnage $character): ?Lieu
    {
        return $character === null ? null : $this->journeys()[$character->getId()]['locations'][0] ?? null;
    }

    public function map(?Personnage $character, bool $includeHiddenLocation = false): array
    {
        $freePosition = $this->freePosition($character);
        $currentPosition = $freePosition === null ? $this->position($character) : null;
        $knownPosition = $currentPosition !== null && ($includeHiddenLocation || $this->visibility->isListed($currentPosition));
        $mapVisible = $freePosition !== null || $knownPosition;

        return [
            'free_position' => $freePosition,
            'current_position' => $currentPosition,
            'known_position' => $knownPosition,
            'map_visible' => $mapVisible,
            'locations' => $mapVisible ? $this->locationRepository->findAllSorted() : [],
        ];
    }

    private function listWanderers(array $sheets): array
    {
        $wanderers = [];
        foreach ($sheets as $sheet) {
            $character = $sheet->getPersonnage();

            if ($this->visibility->isListed($character)) {
                $wanderers[$character->getId()] = [
                    'character' => $character,
                    'locX' => $sheet->getLocX(),
                    'locY' => $sheet->getLocY(),
                ];
            }
        }

        return $wanderers;
    }

    private function journeys(): array
    {
        if ($this->journeys !== null) {
            return $this->journeys;
        }

        $this->journeys = [];
        foreach ($this->participationRepository->findPlacedForLivingPlayers() as $participation) {
            $character = $participation->getPersonnage();
            $scene = $participation->getScene();

            if (!$this->visibility->isReadable($scene->getEpisodeParent()) || !$this->visibility->isListed($character)) {
                continue;
            }

            $id = $character->getId();
            $locations = $this->journeys[$id]['locations'] ?? [];
            $last = end($locations);

            $this->journeys[$id]['character'] = $character;
            if ($last === false || $last->getId() !== $scene->getLieu()->getId()) {
                $this->journeys[$id]['locations'][] = $scene->getLieu();
            }
        }

        return $this->journeys;
    }
}
