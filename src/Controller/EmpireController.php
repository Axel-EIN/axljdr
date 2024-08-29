<?php

namespace App\Controller;

use App\Entity\Clan;
use App\Entity\Lieu;
use App\Entity\Lore;
use App\Entity\Archive;
use App\Repository\ClanRepository;
use App\Repository\LieuRepository;
use App\Repository\LoreRepository;
use App\Repository\ArchiveRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmpireController extends AbstractController
{
    /**
     * @Route("/empire", name="empire")
     */
    public function afficherEmpire(ClanRepository $clanRepository,
                                   ArchiveRepository $archiveRepository,
                                   LieuRepository $lieuRepository,
                                   LoreRepository $loreRepository): Response
    {
        $clansMajeurs = $clanRepository->findAllMajeurs();
        $clansAutres = $clanRepository->findAllAutres();
        $archives = $archiveRepository->findall();
        $lieux = $lieuRepository->findAllSorted();
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

        $header_classname = 'empire';
        $header_up = "Univers du Jeu";
        $header_down = "L'Empire de Rokugan";
        $category = 'empire';

        return $this->render('empire/index.html.twig', [
            'clansMajeurs' => $clansMajeurs,
            'clansAutres' => $clansAutres,
            'archives' => $archives,
            'lieux' => $lieux,
            'lores' => $lores,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category
        ]);
    }

    /**
     * @Route("/empire/clan/{id}", name="empire_clan")
     */
    public function afficherClan(Clan $clan, ClanRepository $clanRepository): Response
    {
        if ($clan->getEstMajeur() == 1)
            $toutClans = $clanRepository->findAllMajeurs();
        else
            $toutClans = $clanRepository->findAllAutres();

        $autresClans = $clanRepository->findAllExceptOne($clan->getId());
        shuffle($autresClans);

        return $this->render('empire/clan.html.twig', [
            'clan' => $clan,
            'nom' => $clan->getNom(),
            'entity' => 'clan',
            'category' => 'empire',
            'un_element' => $clan,
            'autres_clans' => $autresClans,
            'toutClans' => $toutClans,
        ]);
    }

    /**
     * @Route("/empire/archive/{id}", name="empire_archive")
     */
    public function afficherArchive(Archive $archive, ArchiveRepository $archiveRepository): Response
    {
        $archives = $archiveRepository->findall();
        $autresArchives = $archiveRepository->findAllExceptOne($archive->getId());

        return $this->render('empire/archive.html.twig', [
            'un_element' => $archive,
            'nom' => $archive->getTitre(),
            'entity' => 'archive',
            'category' => 'empire',
            'archives' => $archives,
            'autresArchives' => $autresArchives
        ]);
    }

    /**
     * @Route("/empire/lieu/{id}", name="empire_lieu")
     */
    public function afficherLieu(Lieu $lieu, LieuRepository $lieuRepository): Response
    {
        $lieux = $lieuRepository->findAllSorted();
        $autresLieux = $lieuRepository->findAllExceptOne($lieu->getId());
        shuffle($autresLieux);
        $autresLieux = array_slice($autresLieux, 0, 12);

        return $this->render('empire/lieu.html.twig', [
            'un_element' => $lieu,
            'nom' => $lieu->getNom(),
            'entity' => 'lieu',
            'category' => 'empire',
            'lieux' => $lieux,
            'autresLieux' => $autresLieux
        ]);
    }

    /**
     * @Route("/empire/lore/{id}", name="empire_lore")
     */
    public function afficherLore(Lore $lore, LoreRepository $loreRepository): Response
    {
        $lores = $loreRepository->findall();
        $autresLores = $loreRepository->findAllExceptOne($lore->getId());

        return $this->render('empire/lore.html.twig', [
            'un_element' => $lore,
            'nom' => $lore->getNom(),
            'entity' => 'lore',
            'category' => 'empire',
            'lores' => $lores,
            'autresLores' => $autresLores,
        ]);
    }
}