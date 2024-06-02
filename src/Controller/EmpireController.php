<?php

namespace App\Controller;

use App\Entity\Clan;
use App\Entity\Lieu;
use App\Entity\Archive;
use App\Repository\ClanRepository;
use App\Repository\LieuRepository;
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
                                   LieuRepository $lieuRepository): Response
    {
        $clansMajeurs = $clanRepository->findAllMajeurs();
        $clansAutres = $clanRepository->findAllAutres();
        $archives = $archiveRepository->findall();
        $lieux = $lieuRepository->findall();

        $sections = [];
        $sections[0]['name'] = "Archives";
        $sections[0]['entity'] = 'archive';
        $sections[0]['label_one'] = "une archive";
        $sections[0]['titleLight'] = 'Les';
        $sections[0]['titleStrong'] = 'Archives';


        $sections[1]['name'] = "Factions";
        $sections[1]['entity'] = 'clan';
        $sections[1]['label_one'] = "une faction";
        $sections[1]['titleLight'] = 'Les';
        $sections[1]['titleStrong'] = 'Factions';

        $sections[2]['name'] = "Lieux";
        $sections[2]['entity'] = 'lieu';
        $sections[2]['label_one'] = "un lieu";
        $sections[2]['titleLight'] = 'Les';
        $sections[2]['titleStrong'] = 'Lieux';

        $header_classname = 'empire';
        $header_up = "Univers du Jeu";
        $header_down = "L'Empire de Rokugan";
        $category = 'empire';

        return $this->render('empire/index.html.twig', [
            'clansMajeurs' => $clansMajeurs,
            'clansAutres' => $clansAutres,
            'archives' => $archives,
            'lieux' => $lieux,
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
    public function afficherClan(Clan $clan): Response
    {
        return $this->render('empire/clan.html.twig', [
            'clan' => $clan,
            'nom' => $clan->getNom(),
            'entity' => 'clan',
            'category' => 'empire',
            'un_element' => $clan,
        ]);
    }

    /**
     * @Route("/empire/archive/{id}", name="empire_archive")
     */
    public function afficherArchive(Archive $archive): Response
    {
        return $this->render('empire/archive.html.twig', [
            'un_element' => $archive,
            'nom' => $archive->getTitre(),
            'entity' => 'archive',
            'category' => 'empire',
        ]);
    }

    /**
     * @Route("/empire/lieu/{id}", name="empire_lieu")
     */
    public function afficherLieu(Lieu $lieu): Response
    {
        return $this->render('empire/lieu.html.twig', [
            'un_element' => $lieu,
            'nom' => $lieu->getNom(),
            'entity' => 'lieu',
            'category' => 'empire',
        ]);
    }
}
