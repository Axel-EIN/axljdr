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
  public function afficherWorld(ClanRepository $clanRepository,
                                 ArchiveRepository $archiveRepository,
                                 LieuRepository $lieuRepository,
                                 LoreRepository $loreRepository,
                                 Visibility $visibility): Response
  {
    $factions = array_fill_keys(array_keys(FactionType::GROUPS), []);
    foreach ($clanRepository->findAllSorted() as $clan) {
      if ($visibility->isListed($clan)) {
        $factions[$clan->getType()][] = $clan;
      }
    }
    $archives = $archiveRepository->findall();
    $groupesLieux = $this->grouperLieux($lieuRepository->findAllSorted(), $visibility);
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
      'groupesLieux' => $groupesLieux,
      'lores' => $lores,
      'sections' => $sections,
      'header_classname' => $header_classname,
      'header_up' => $header_up,
      'header_down' => $header_down,
      'category' => $category
    ]);
  }

  private function grouperLieux(array $lieux, Visibility $visibility): array
  {
    $titres = array_column($this->getParameter('factions'), 'titre', 'nom');
    $ordre = array_merge(
      array_map(fn (string $nom) => 'clan-' . $nom, array_keys($titres)),
      array_map(fn (int $type) => 'type-' . $type, array_keys(FactionType::GROUPS)),
      ['autres']
    );

    $groupes = [];
    foreach ($lieux as $lieu) {
      if (!$visibility->isListed($lieu)) {
        continue;
      }

      $clan = $lieu->getClan();
      $majeur = $clan !== null && $clan->getType() === FactionType::MAJEUR;

      if ($clan === null || ($majeur && !isset($titres[$clan->getNom()]))) {
        $cle = 'autres';
        $groupes[$cle]['titre'] = 'Autres';
      } else {
        $cle = $majeur ? 'clan-' . $clan->getNom() : 'type-' . $clan->getType();
        $groupes[$cle]['titre'] = $majeur ? $titres[$clan->getNom()] : FactionType::GROUPS[$clan->getType()];
        if ($visibility->isListed($clan)) {
          $groupes[$cle]['clans'][$clan->getId()] = $clan;
        }
      }

      $groupes[$cle]['lieux'][] = $lieu;
    }

    return array_values(array_filter(array_map(fn (string $cle) => $groupes[$cle] ?? null, $ordre)));
  }

  /**
   * @Route("/world/clan/{id}", name="world_clan")
   */
  public function afficherClan(Clan $clan, ClanRepository $clanRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $clan, 'clan', 'world')) {
      return $response;
    }

    $toutClans = array_filter(
      $clanRepository->findByTypes(FactionType::siblingTypes($clan->getType())),
      fn (Clan $autre) => $visibility->isListed($autre)
    );

    return $this->render('world/clan.html.twig', [
      'clan' => $clan,
      'nom' => $clan->getNom(),
      'entity' => 'clan',
      'category' => 'world',
      'un_element' => $clan,
      'toutClans' => $toutClans,
    ]);
  }

  /**
   * @Route("/world/archive/{id}", name="world_archive")
   */
  public function afficherArchive(Archive $archive, ArchiveRepository $archiveRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $archive, 'archive', 'world')) {
      return $response;
    }

    $archives = $archiveRepository->findall();
    $autresArchives = $archiveRepository->findAllExceptOne($archive->getId());

    return $this->render('world/archive.html.twig', [
      'un_element' => $archive,
      'nom' => $archive->getTitre(),
      'entity' => 'archive',
      'category' => 'world',
      'archives' => $archives,
      'autresArchives' => $autresArchives
    ]);
  }

  /**
   * @Route("/world/lieu/{id}", name="world_lieu")
   */
  public function afficherLocation(Lieu $lieu, LieuRepository $lieuRepository, ClasseurHistorique $classeur, SaisonRepository $saisonRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $lieu, 'lieu', 'world')) {
      return $response;
    }

    $lieux = $lieuRepository->findAllSorted();
    $autresLieux = $lieuRepository->findAllExceptOne($lieu->getId());
    shuffle($autresLieux);
    $autresLieux = array_slice($autresLieux, 0, 12);

    $historique = $classeur->pourLieu($lieu);

    return $this->render('world/location.html.twig', [
      'un_element' => $lieu,
      'nom' => $lieu->getNom(),
      'entity' => 'lieu',
      'category' => 'world',
      'lieux' => $lieux,
      'autresLieux' => $autresLieux,
      'historique' => $historique,
      'saison_courante' => $saisonRepository->findCourante()?->getNumero(),
    ]);
  }

  /**
   * @Route("/world/lore/{id}", name="world_lore")
   */
  public function afficherLore(Lore $lore, LoreRepository $loreRepository, Visibility $visibility): Response
  {
    if ($response = $this->accessGuard($visibility, $lore, 'lore', 'world')) {
      return $response;
    }

    return $this->render('world/lore.html.twig', [
      'un_element' => $lore,
      'nom' => $lore->getNom(),
      'entity' => 'lore',
      'category' => 'world',
      'autresLores' => $loreRepository->findAllExceptOne($lore->getId()),
    ]);
  }
}
