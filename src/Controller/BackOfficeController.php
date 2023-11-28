<?php

namespace App\Controller;

use App\Repository\ClanRepository;
use App\Repository\LieuRepository;
use App\Repository\RuleRepository;
use App\Repository\EcoleRepository;
use App\Repository\ObjetRepository;
use App\Repository\SceneRepository;
use App\Repository\ClasseRepository;
use App\Repository\SaisonRepository;
use App\Repository\ArchiveRepository;
use App\Repository\EpisodeRepository;
use App\Repository\FamilleRepository;
use App\Repository\LibraryRepository;
use App\Repository\AvantageRepository;
use App\Repository\ChapitreRepository;
use App\Repository\CompetenceRepository;
use App\Repository\PersonnageRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\FichePersonnageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BackOfficeController extends AbstractController
{
    /**
     * @Route("/back-office", name="back_office")
     */
    public function viewBackOffice(
        SaisonRepository $saisonRepository,
        ChapitreRepository $chapitreRepository,
        EpisodeRepository $episodeRepository,
        SceneRepository $sceneRepository,

        ClanRepository $clanRepository,
        FamilleRepository $familleRepository,
        ArchiveRepository $archiveRepository,
        LieuRepository $lieuRepository,

        RuleRepository $ruleRepository,
        LibraryRepository $libraryRepository,
        ClasseRepository $classeRepository,
        EcoleRepository $ecoleRepository,
        AvantageRepository $avantageRepository,
        CompetenceRepository $competenceRepository,
        ObjetRepository $objetRepository,

        PersonnageRepository $personnageRepository,
        FichePersonnageRepository $fichePersonnageRepository,

        UtilisateurRepository $utilisateurRepository ): Response
    {

        if(
            !$this->isGranted('ROLE_ADMIN')
         || !$this->isGranted('ROLE_MJ')
         )
            throw new \Exception('Permission denied!');

        // AVENTURE
        $nbrSaisons = $saisonRepository->countSaisons();
        $lastSaison = $saisonRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrChapitres = $chapitreRepository->countChapitres();
        $lastChapitre = $chapitreRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrEpisodes = $episodeRepository->countEpisodes();
        $lastEpisode = $episodeRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrScenes = $sceneRepository->countScenes();
        $lastScene = $sceneRepository->findOneBy(array(),array('id' => 'DESC'));

        // CHARACTERS
        $nbrPJs = $personnageRepository->countPJs();
        $lastPJ = $personnageRepository->findOneBy(array("estPj" => "1"),array('id' => 'DESC'));

        $nbrPNJs = $personnageRepository->countPNJs();
        $lastPNJ = $personnageRepository->findOneBy(array("estPj" => "0"),array('id' => 'DESC'));

        $nbrFiches = $fichePersonnageRepository->countFiches();
        $lastFiche = $fichePersonnageRepository->findOneBy(array(),array('id' => 'DESC'));

        // EMPIRE
        $nbrClans = $clanRepository->countClans();
        $lastClan = $clanRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrFamilles = $familleRepository->countFamilles();
        $lastFamille = $familleRepository->findOneBy(array(),array('id' => 'DESC'));
        
        $nbrArchives = $archiveRepository->countArchives();
        $lastArchive = $archiveRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrLieux = $lieuRepository->countLieux();
        $lastLieu = $lieuRepository->findOneBy(array(),array('id' => 'DESC'));

        // RULES
        $nbrRules = $ruleRepository->countRules();
        $lastRule = $ruleRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrLibraries = $libraryRepository->countLibraries();
        $lastLibrary = $libraryRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrClasses = $classeRepository->countClasses();
        $lastClasse = $classeRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrEcoles = $ecoleRepository->countEcoles();
        $lastEcole = $ecoleRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrAvantages = $avantageRepository->countAvantages();
        $lastAvantage = $avantageRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrCompetences = $competenceRepository->countCompetences();
        $lastCompetence = $competenceRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrObjets = $objetRepository->countObjets();
        $lastObjet = $objetRepository->findOneBy(array(),array('id' => 'DESC'));

        // USER
        $nbrUtilisateurs = $utilisateurRepository->countUtilisateurs();
        $lastUtilisateur = $utilisateurRepository->findOneBy(array(),array('id' => 'DESC'));

        return $this->render('back_office/index.html.twig', [
            'controller_name' => 'BackOfficeController',

            'nbrSaisons' => $nbrSaisons,
            'lastSaison' => $lastSaison,
            'nbrChapitres' => $nbrChapitres,
            'lastChapitre' => $lastChapitre,
            'nbrEpisodes' => $nbrEpisodes,
            'lastEpisode' => $lastEpisode,
            'nbrScenes' => $nbrScenes,
            'lastScene' => $lastScene,

            'nbrPJs' => $nbrPJs,
            'lastPJ' => $lastPJ,
            'nbrPNJs' => $nbrPNJs,
            'lastPNJ' => $lastPNJ,
            'nbrFiches' => $nbrFiches,
            'lastFiche' => $lastFiche,

            'nbrClans' => $nbrClans,
            'lastClan' => $lastClan,
            'nbrFamilles' => $nbrFamilles,
            'lastFamille' => $lastFamille,
            'nbrArchives' => $nbrArchives,
            'lastArchive' => $lastArchive,
            'nbrLieux' => $nbrLieux,
            'lastLieu' => $lastLieu,

            'nbrRules' => $nbrRules,
            'lastRule' => $lastRule,
            'nbrLibraries' => $nbrLibraries,
            'lastLibrary' => $lastLibrary,
            'nbrClasses' => $nbrClasses,
            'lastClasse' => $lastClasse,
            'nbrEcoles' => $nbrEcoles,
            'lastEcole' => $lastEcole,
            'nbrAvantages' => $nbrAvantages,
            'lastAvantage' => $lastAvantage,
            'nbrCompetences' => $nbrCompetences,
            'lastCompetence' => $lastCompetence,
            'nbrObjets' => $nbrObjets,
            'lastObjet' => $lastObjet,

            'nbrUtilisateurs' => $nbrUtilisateurs,
            'lastUtilisateur' => $lastUtilisateur,
        ]);
    }
}