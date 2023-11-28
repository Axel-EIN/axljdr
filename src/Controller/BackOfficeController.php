<?php

namespace App\Controller;

use App\Repository\SaisonRepository;
use App\Repository\ChapitreRepository;
use App\Repository\EpisodeRepository;
use App\Repository\SceneRepository;
use App\Repository\PersonnageRepository;
use App\Repository\FichePersonnageRepository;
use App\Repository\ClanRepository;
use App\Repository\FamilleRepository;
use App\Repository\ArchiveRepository;
use App\Repository\LieuRepository;
use App\Repository\RuleRepository;
use App\Repository\ClasseRepository;
use App\Repository\EcoleRepository;
use App\Repository\AvantageRepository;
use App\Repository\CompetenceRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    /**
     * @Route("/back-office", name="back_office")
     */
    public function viewBackOffice(SaisonRepository $saisonRepository,
                                       ChapitreRepository $chapitreRepository,
                                       EpisodeRepository $episodeRepository,
                                       SceneRepository $sceneRepository,

                                       ClanRepository $clanRepository,
                                       FamilleRepository $familleRepository,
                                       ArchiveRepository $archiveRepository,
                                       LieuRepository $lieuRepository,

                                       RuleRepository $ruleRepository,
                                       ClasseRepository $classeRepository,
                                       EcoleRepository $ecoleRepository,
                                       AvantageRepository $avantageRepository,
                                       CompetenceRepository $competenceRepository,

                                       PersonnageRepository $personnageRepository,
                                       FichePersonnageRepository $fichePersonnageRepository,

                                       UtilisateurRepository $utilisateurRepository
                                       ): Response
    {

        if( !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MJ') ) // Si pas Admin ou pas MJ alors redirection
            throw new \Exception('Permission denied!');

        // AVENTURE
        $nbrSaisons = $saisonRepository->countSaisons();
        $dernierSaison = $saisonRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrChapitres = $chapitreRepository->countChapitres();
        $dernierChapitre = $chapitreRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrEpisodes = $episodeRepository->countEpisodes();
        $dernierEpisode = $episodeRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrScenes = $sceneRepository->countScenes();
        $dernierScene = $sceneRepository->findOneBy(array(),array('id' => 'DESC'));

        // CHARACTERS
        $nbrPJs = $personnageRepository->countPJs();
        $dernierPJ = $personnageRepository->findOneBy(array("estPj" => "1"),array('id' => 'DESC'));

        $nbrPNJs = $personnageRepository->countPNJs();
        $dernierPNJ = $personnageRepository->findOneBy(array("estPj" => "0"),array('id' => 'DESC'));

        $nbrFiches = $fichePersonnageRepository->countFiches();
        $derniereFiche = $fichePersonnageRepository->findOneBy(array(),array('id' => 'DESC'));

        // EMPIRE
        $nbrClans = $clanRepository->countClans();
        $dernierClan = $clanRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrFamilles = $familleRepository->countFamilles();
        $derniereFamille = $familleRepository->findOneBy(array(),array('id' => 'DESC'));
        
        $nbrArchives = $archiveRepository->countArchives();
        $derniereArchive = $archiveRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrLieux = $lieuRepository->countLieux();
        $dernierLieu = $lieuRepository->findOneBy(array(),array('id' => 'DESC'));

        // RULES
        $nbrRules = $ruleRepository->countRules();
        $derniereRule = $ruleRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrClasses = $classeRepository->countClasses();
        $dernierClasse = $classeRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrEcoles = $ecoleRepository->countEcoles();
        $dernierEcole = $ecoleRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrAvantages = $avantageRepository->countAvantages();
        $dernierAvantage = $avantageRepository->findOneBy(array(),array('id' => 'DESC'));

        $nbrCompetences = $competenceRepository->countCompetences();
        $derniereCompetence = $competenceRepository->findOneBy(array(),array('id' => 'DESC'));

        // USER
        $nbrUtilisateurs = $utilisateurRepository->countUtilisateurs();
        $dernierUtilisateur = $utilisateurRepository->findOneBy(array(),array('id' => 'DESC'));

        return $this->render('back_office/index.html.twig', [
            'controller_name' => 'BackOfficeController',

            'nbrSaisons' => $nbrSaisons,
            'dernierSaison' => $dernierSaison,
            'nbrChapitres' => $nbrChapitres,
            'dernierChapitre' => $dernierChapitre,
            'nbrEpisodes' => $nbrEpisodes,
            'dernierEpisode' => $dernierEpisode,
            'nbrScenes' => $nbrScenes,
            'dernierScene' => $dernierScene,

            'nbrPJs' => $nbrPJs,
            'dernierPJ' => $dernierPJ,
            'nbrPNJs' => $nbrPNJs,
            'dernierPNJ' => $dernierPNJ,
            'nbrFiches' => $nbrFiches,
            'derniereFiche' => $derniereFiche,

            'nbrClans' => $nbrClans,
            'dernierClan' => $dernierClan,
            'nbrFamilles' => $nbrFamilles,
            'derniereFamille' => $derniereFamille,
            'nbrArchives' => $nbrArchives,
            'derniereArchive' => $derniereArchive,
            'nbrLieux' => $nbrLieux,
            'dernierLieu' => $dernierLieu,

            'nbrRules' => $nbrRules,
            'derniereRule' => $derniereRule,
            'nbrClasses' => $nbrClasses,
            'dernierClasse' => $dernierClasse,
            'nbrEcoles' => $nbrEcoles,
            'dernierEcole' => $dernierEcole,
            'nbrAvantages' => $nbrAvantages,
            'dernierAvantage' => $dernierAvantage,
            'nbrCompetences' => $nbrCompetences,
            'derniereCompetence' => $derniereCompetence,

            'nbrUtilisateurs' => $nbrUtilisateurs,
            'dernierUtilisateur' => $dernierUtilisateur,
        ]);
    }
}