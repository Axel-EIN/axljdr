<?php

namespace App\Controller;

use App\Repository\ClanRepository;
use App\Repository\LieuRepository;
use App\Repository\RuleRepository;
use App\Repository\SortRepository;
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
        SortRepository $sortRepository,

        PersonnageRepository $personnageRepository,
        FichePersonnageRepository $fichePersonnageRepository,

        UtilisateurRepository $utilisateurRepository ): Response
    {

        if( !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MJ') )
            throw new \Exception('Permission denied!');

        $admin_elements = [];

        // SAISON
        $admin_elements[0]['element'] = 'saison' ;
        $admin_elements[0]['label'] = 'Saisons';
        $admin_elements[0]['genre'] = 'F';
        $admin_elements[0]['categorie'] = 'AVENTURE';
        $admin_elements[0]['nbr'] = $saisonRepository->countSaisons();
        $lastSaison = $saisonRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSaison)) {
            $admin_elements[0]['last'] = $lastSaison;
            $admin_elements[0]['nom'] = $lastSaison->getTitre();
            $admin_elements[0]['image'] = $lastSaison->getImage();
        }

        // CHAPITRE
        $admin_elements[1]['element'] = 'chapitre' ;
        $admin_elements[1]['label'] = 'Chapitres';
        $admin_elements[1]['genre'] = 'M';
        $admin_elements[1]['categorie'] = 'AVENTURE';
        $admin_elements[1]['nbr'] = $chapitreRepository->countChapitres();
        $lastChapitre = $chapitreRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastChapitre)) {
            $admin_elements[1]['last'] = $lastChapitre;
            $admin_elements[1]['nom'] = $lastChapitre->getTitre();
            $admin_elements[1]['image'] = $lastChapitre->getImage();
        }

        // EPISODE
        $admin_elements[2]['element'] = 'episode' ;
        $admin_elements[2]['label'] = 'Épisodes';
        $admin_elements[2]['genre'] = 'M';
        $admin_elements[2]['categorie'] = 'AVENTURE';
        $admin_elements[2]['nbr'] = $episodeRepository->countEpisodes();
        $lastEpisode = $episodeRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastEpisode)) {
            $admin_elements[2]['last'] = $lastEpisode;
            $admin_elements[2]['nom'] = $lastEpisode->getTitre();
            $admin_elements[2]['image'] = $lastEpisode->getImage();
        }

        // SCENE
        $admin_elements[3]['element'] = 'scene' ;
        $admin_elements[3]['label'] = 'Scènes';
        $admin_elements[3]['genre'] = 'F';
        $admin_elements[3]['categorie'] = 'AVENTURE';
        $admin_elements[3]['nbr'] = $sceneRepository->countScenes();
        $lastScene = $sceneRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastScene)) {
            $admin_elements[3]['last'] = $lastScene;
            $admin_elements[3]['nom'] = $lastScene->getTitre();
            $admin_elements[3]['image'] = $lastScene->getImage();
        }

        // CLAN
        $admin_elements[4]['element'] = 'clan' ;
        $admin_elements[4]['label'] = 'Factions';
        $admin_elements[4]['genre'] = 'F';
        $admin_elements[4]['categorie'] = 'EMPIRE';
        $admin_elements[4]['nbr'] = $clanRepository->countClans();
        $lastClan = $clanRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastClan)) {
            $admin_elements[4]['last'] = $lastClan;
            $admin_elements[4]['nom'] = $lastClan->getNom();
            $admin_elements[4]['image'] = $lastClan->getMon();
        }

        // FAMILLE
        $admin_elements[5]['element'] = 'famille' ;
        $admin_elements[5]['label'] = 'Familles';
        $admin_elements[5]['genre'] = 'F';
        $admin_elements[5]['categorie'] = 'EMPIRE';
        $admin_elements[5]['nbr'] = $familleRepository->countFamilles();
        $lastFamille = $familleRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastFamille)) {
            $admin_elements[5]['last'] = $lastFamille;
            $admin_elements[5]['nom'] = $lastFamille->getNom();
            $admin_elements[5]['image'] = $lastFamille->getMon();
        }
        
        // ARCHIVE
        $admin_elements[6]['element'] = 'archive' ;
        $admin_elements[6]['label'] = 'Archives';
        $admin_elements[6]['genre'] = 'F';
        $admin_elements[6]['categorie'] = 'EMPIRE';
        $admin_elements[6]['nbr'] = $archiveRepository->countArchives();
        $lastArchive = $archiveRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastArchive)) {
            $admin_elements[6]['last'] = $lastArchive;
            $admin_elements[6]['nom'] = $lastArchive->getTitre();
            $admin_elements[6]['image'] = $lastArchive->getImage();
        }

        // LIEU
        $admin_elements[7]['element'] = 'lieu' ;
        $admin_elements[7]['label'] = 'Lieux';
        $admin_elements[7]['genre'] = 'M';
        $admin_elements[7]['categorie'] = 'EMPIRE';
        $admin_elements[7]['nbr'] = $lieuRepository->countLieux();
        $lastLieu = $lieuRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastLieu)) {
            $admin_elements[7]['last'] = $lastLieu;
            $admin_elements[7]['nom'] = $lastLieu->getNom();
            $admin_elements[7]['image'] = $lastLieu->getImage();
        }

        // PERSONNAGE
        $admin_elements[8]['element'] = 'personnage' ;
        $admin_elements[8]['label'] = 'Personnages';
        $admin_elements[8]['genre'] = 'M';
        $admin_elements[8]['categorie'] = 'PERSONNAGES';
        $admin_elements[8]['nbr'] = $personnageRepository->countPJs();
        $lastPersonnage = $personnageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastPersonnage)) {
            $admin_elements[8]['last'] = $lastPersonnage;
            $admin_elements[8]['nom'] = $lastPersonnage->getNom();
            $admin_elements[8]['image'] = $lastPersonnage->getIcone();
        }

        // FICHE
        $admin_elements[9]['element'] = 'fiche' ;
        $admin_elements[9]['label'] = 'Fiches';
        $admin_elements[9]['genre'] = 'F';
        $admin_elements[9]['categorie'] = 'PERSONNAGES';
        $admin_elements[9]['nbr'] = $fichePersonnageRepository->countFiches();
        $lastFiche = $fichePersonnageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastFiche)) {
            $admin_elements[9]['last'] = $lastFiche;
            $admin_elements[9]['nom'] = $lastFiche->getPersonnage()->getNom();
            $admin_elements[9]['image'] = $lastFiche->getPersonnage()->getIcone();
        }

        // REGLES
        $admin_elements[10]['element'] = 'rule' ;
        $admin_elements[10]['label'] = 'Règles';
        $admin_elements[10]['genre'] = 'F';
        $admin_elements[10]['categorie'] = 'REGLES';
        $admin_elements[10]['nbr'] = $ruleRepository->countRules();
        $lastRule = $ruleRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastRule)) {
            $admin_elements[10]['last'] = $lastRule;
            $admin_elements[10]['nom'] = $lastRule->getNom();
            $admin_elements[10]['image'] = $lastRule->getImage();
        }

        // LIBRARY
        $admin_elements[11]['element'] = 'library' ;
        $admin_elements[11]['label'] = 'Bibliothèques';
        $admin_elements[11]['genre'] = 'F';
        $admin_elements[11]['categorie'] = 'REGLES';
        $admin_elements[11]['nbr'] = $libraryRepository->countLibraries();
        $lastLibrary = $libraryRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastLibrary)) {
            $admin_elements[11]['last'] = $lastLibrary;
            $admin_elements[11]['nom'] = $lastLibrary->getNom();
            $admin_elements[11]['image'] = $lastLibrary->getImage();
        }

        // CLASSE
        $admin_elements[12]['element'] = 'classe' ;
        $admin_elements[12]['label'] = 'Classes';
        $admin_elements[12]['genre'] = 'F';
        $admin_elements[12]['categorie'] = 'REGLES';
        $admin_elements[12]['nbr'] = $classeRepository->countClasses();
        $lastClasse = $classeRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastClasse)) {
            $admin_elements[12]['last'] = $lastClasse;
            $admin_elements[12]['nom'] = $lastClasse->getNom();
            $admin_elements[12]['image'] = $lastClasse->getImage();
        }

        // ECOLE
        $admin_elements[13]['element'] = 'ecole' ;
        $admin_elements[13]['label'] = 'Écoles';
        $admin_elements[13]['genre'] = 'F';
        $admin_elements[13]['categorie'] = 'REGLES';
        $admin_elements[13]['nbr'] = $ecoleRepository->countEcoles();
        $lastEcole = $ecoleRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastEcole)) {
            $admin_elements[13]['last'] = $lastEcole;
            $admin_elements[13]['nom'] = $lastEcole->getNom();
            $admin_elements[13]['image'] = $lastEcole->getImage();
        }

        // AVANTAGE
        $admin_elements[14]['element'] = 'avantage' ;
        $admin_elements[14]['label'] = 'Avantages / Désavantages';
        $admin_elements[14]['genre'] = 'M';
        $admin_elements[14]['categorie'] = 'REGLES';
        $admin_elements[14]['nbr'] = $avantageRepository->countAvantages();
        $lastAvantage = $avantageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastAvantage)) {
            $admin_elements[14]['last'] = $lastAvantage;
            $admin_elements[14]['nom'] = $lastAvantage->getNom();
            $admin_elements[14]['image'] = '';
        }

        // COMPETENCE
        $admin_elements[15]['element'] = 'competence' ;
        $admin_elements[15]['label'] = 'Compétences';
        $admin_elements[15]['genre'] = 'F';
        $admin_elements[15]['categorie'] = 'REGLES';
        $admin_elements[15]['nbr'] = $competenceRepository->countCompetences();
        $lastCompetence = $competenceRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastCompetence)) {
            $admin_elements[15]['last'] = $lastCompetence;
            $admin_elements[15]['nom'] = $lastCompetence->getNom();
            $admin_elements[15]['image'] = '';
        }

        // OBJET
        $admin_elements[16]['element'] = 'objet' ;
        $admin_elements[16]['label'] = 'Objets';
        $admin_elements[16]['genre'] = 'M';
        $admin_elements[16]['categorie'] = 'REGLES';
        $admin_elements[16]['nbr'] = $objetRepository->countObjets();
        $lastObjet = $objetRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastObjet)) {
            $admin_elements[16]['last'] = $lastObjet;
            $admin_elements[16]['nom'] = $lastObjet->getNom();
            $admin_elements[16]['image'] = $lastObjet->getImage();
        }

        // SORT
        $admin_elements[17]['element'] = 'sort' ;
        $admin_elements[17]['label'] = 'Sorts';
        $admin_elements[17]['genre'] = 'M';
        $admin_elements[17]['categorie'] = 'REGLES';
        $admin_elements[17]['nbr'] = $sortRepository->countSorts();
        $lastSort = $sortRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSort)) {
            $admin_elements[17]['last'] = $lastSort;
            $admin_elements[17]['nom'] = $lastSort->getNom();
            $admin_elements[17]['image'] = '';
        } else {
            $admin_elements[17]['last'] = '';
            $admin_elements[17]['nom'] = '';
            $admin_elements[17]['image'] = '';
        }

        // USER
        $nbrUtilisateurs = $utilisateurRepository->countUtilisateurs();
        $lastUtilisateur = $utilisateurRepository->findOneBy(array(),array('id' => 'DESC'));

        return $this->render('back_office/index.html.twig', [
            'nbrUtilisateurs' => $nbrUtilisateurs,
            'lastUtilisateur' => $lastUtilisateur,
            'admin_elements' => $admin_elements,
        ]);
    }
}