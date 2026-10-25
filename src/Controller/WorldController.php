<?php

namespace App\Controller;

use App\Entity\Clan;
use App\Entity\FactionType;
use App\Entity\Lieu;
use App\Entity\Lore;
use App\Entity\Archive;
use App\Repository\ClanRepository;
use App\Repository\LieuRepository;
use App\Repository\LoreRepository;
use App\Repository\ArchiveRepository;
use App\Repository\SaisonRepository;
use App\Service\ClasseurHistorique;
use App\Service\ClasseurLieux;
use App\Service\Visibility;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WorldController extends AbstractController
{
  use VisibilityTrait;

  /**
   * @Route("/world", name="world")
   */
  public function index(ClanRepository $clanRepository,
                        ArchiveRepository $archiveRepository,
                        LieuRepository $locationRepository,
                        LoreRepository $loreRepository,
                        Visibility $visibility,
                        ClasseurLieux $locationGrouper): Response
  {
    $factions = array_fill_keys(array_keys(FactionType::GROUPS), []);
    foreach ($clanRepository->findAllSorted() as $clan) {
      if ($visibility->isListed($clan)) {
        $factions[$clan->getType()][] = $clan;
      }
    }
    $archives = $archiveRepository->findall();
    $locationGroups = $locationGrouper->parFaction($locationRepository->findAllSorted());
    $lores = $loreRepository->findall();

    $sections = [];
    $i = 0;
    $sections[$i]['name'] = "Archives";
    $sections[$i]['entity'] = 'archive';
    $sections[$i]['label_one'] = "une archive";
    $sections[$i]['titleLight'] = 'Les ';
    $sections[$i]['titleStrong'] = 'Archives';

    $i++;
    $sections[$i]['name'] = "Factions";
    $sections[$i]['entity'] = 'clan';
    $sections[$i]['label_one'] = "une faction";
    $sections[$i]['titleLight'] = 'Les ';
    $sections[$i]['titleStrong'] = 'Factions';

    $i++;
    $sections[$i]['name'] = "Lieux";
    $sections[$i]['entity'] = 'lieu';
    $sections[$i]['label_one'] = "un lieu";
    $sections[$i]['titleLight'] = 'Les ';
    $sections[$i]['titleStrong'] = 'Lieux';

    $i++;
    $sections[$i]['name'] = "Lore";
    $sections[$i]['entity'] = 'lore';
    $sections[$i]['label_one'] = "un Lore";
    $sections[$i]['titleLight'] = '';
    $sections[$i]['titleStrong'] = 'Lore';

    $header_classname = 'world';
    $header_up = "Univers du Jeu";
    $header_down = "L'Empire d'Emeraude";
    $category = 'world';

    return $this->render('world/index.html.twig', [
      'factions' => $factions,
      'archives' => $archives,
      'location_groups' => $locationGroups,
      'lores' => $lores,
      'sections' => $sections,
      'header_classname' => $header_classname,
      'header_up' => $header_up,
      'header_down' => $header_down,
      'category' => $category
    ]);
  }

  /**
   * @Route("/world/clan/{id}", name="world_clan")
   */
  public function showClan(Clan $clan, ClanRepository $clanRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $clan, 'clan', 'world')) {
      return $response;
    }

    $allClans = array_filter(
      $clanRepository->findByTypes(FactionType::siblingTypes($clan->getType())),
      fn (Clan $other) => $visibility->isListed($other)
    );

    return $this->render('world/clan.html.twig', [
      'clan' => $clan,
      'nom' => $clan->getNom(),
      'entity' => 'clan',
      'category' => 'world',
      'un_element' => $clan,
      'all_clans' => $allClans,
    ]);
  }

  /**
   * @Route("/world/archive/{id}", name="world_archive")
   */
  public function showArchive(Archive $archive, ArchiveRepository $archiveRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $archive, 'archive', 'world')) {
      return $response;
    }

    $archives = $archiveRepository->findall();
    $otherArchives = $archiveRepository->findAllExceptOne($archive->getId());

    return $this->render('world/archive.html.twig', [
      'un_element' => $archive,
      'nom' => $archive->getTitre(),
      'entity' => 'archive',
      'category' => 'world',
      'archives' => $archives,
      'other_archives' => $otherArchives
    ]);
  }

  /**
   * @Route("/world/location/{id}", name="world_location")
   */
  public function showLocation(Lieu $location, LieuRepository $locationRepository, ClasseurHistorique $classeur, SaisonRepository $seasonRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $location, 'lieu', 'world')) {
      return $response;
    }

    $locations = $locationRepository->findAllSorted();
    $otherLocations = array_values(array_filter(
      $locationRepository->findAllExceptOne($location->getId()),
      fn (Lieu $other) => $other->getClan() === $location->getClan() && $visibility->isReadable($other)
    ));
    shuffle($otherLocations);
    $otherLocations = array_slice($otherLocations, 0, 12);

    $history = $classeur->pourLieu($location);

    return $this->render('world/location.html.twig', [
      'un_element' => $location,
      'nom' => $location->getNom(),
      'entity' => 'lieu',
      'category' => 'world',
      'lieux' => $locations,
      'other_locations' => $otherLocations,
      'historique' => $history,
      'saison_courante' => $seasonRepository->findCourante()?->getNumero(),
    ]);
  }

  /**
   * @Route("/world/lore/{id}", name="world_lore")
   */
  public function showLore(Lore $lore, LoreRepository $loreRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $lore, 'lore', 'world')) {
      return $response;
    }

    return $this->render('world/lore.html.twig', [
      'un_element' => $lore,
      'nom' => $lore->getNom(),
      'entity' => 'lore',
      'category' => 'world',
      'other_lores' => $loreRepository->findAllExceptOne($lore->getId()),
    ]);
  }
}
