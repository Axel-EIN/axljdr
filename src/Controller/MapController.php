<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Repository\LieuRepository;
use App\Service\HistoryRanker;
use App\Service\LocationGrouper;
use App\Service\CurrentPlayer;
use App\Service\Locator;
use App\Service\Visibility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    private const PANEL_HISTORY_LIMIT = 3;

    /**
     * @Route("/map", name="map")
     */
    public function index(Request $request, LieuRepository $locationRepository, Locator $locator, Visibility $visibility, LocationGrouper $locationGrouper, CurrentPlayer $currentPlayer): Response
    {
        $trail = $locator->trail($currentPlayer->navigatingCharacter());

        $target = $locationRepository->find($request->query->getInt('location'));
        if ($target !== null && (!$target->isPlaced() || !$visibility->isListed($target))) {
            $target = null;
        }

        $locations = $locationRepository->findAllSorted();

        return $this->render('map/index.html.twig', [
            'lieux' => $locations,
            'location_groups' => $locationGrouper->byFaction($locations),
            'players' => $locator->players(),
            'positions' => $locator->positions(),
            'wanderers' => $locator->wanderers(),
            'trail' => $trail,
            'target' => $target,
            'main_position' => $trail[0] ?? null,
        ]);
    }

    /**
     * @Route("/map/location/{id}", name="map_location")
     */
    public function showLocationPanel(Lieu $location, Visibility $visibility, Locator $locator, HistoryRanker $historyRanker): Response
    {
        if (!$visibility->isListed($location)) {
            throw $this->createNotFoundException();
        }

        return $this->render('map/panel.html.twig', [
            'location' => $location,
            'present_characters' => $locator->positions()[$location->getId()] ?? [],
            'history' => $visibility->isReadable($location) ? $this->readableHistory($historyRanker->forLocation($location), $visibility) : [],
        ]);
    }

    /**
     * @Route("/map/character/{id}", name="map_character")
     */
    public function showCharacterPanel(Personnage $character, Visibility $visibility, HistoryRanker $historyRanker): Response
    {
        if (!$visibility->isListed($character)) {
            throw $this->createNotFoundException();
        }

        return $this->render('map/panel.html.twig', [
            'character' => $character,
            'history' => $visibility->isReadable($character) ? $this->readableHistory($historyRanker->forCharacter($character), $visibility) : [],
        ]);
    }

    private function readableHistory(array $history, Visibility $visibility): array
    {
        $readable = array_filter($history, function ($block) use ($visibility) {
            return $visibility->isReadable($block['episode']);
        });

        return array_slice(array_values($readable), 0, self::PANEL_HISTORY_LIMIT);
    }
}
