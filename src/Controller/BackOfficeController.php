<?php

namespace App\Controller;

use App\Repository\ClanRepository;
use App\Repository\LieuRepository;
use App\Repository\LoreRepository;
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
        LoreRepository $loreRepository,

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
        $i = 0;

        // SAISON
        $admin_elements[$i]['element'] = 'saison' ;
        $admin_elements[$i]['label'] = 'Saisons';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'AVENTURE';
        $admin_elements[$i]['nbr'] = $saisonRepository->countSaisons();
        $lastSaison = $saisonRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSaison)) {
            $admin_elements[$i]['last'] = $lastSaison;
            $admin_elements[$i]['nom'] = $lastSaison->getTitre();
            $admin_elements[$i]['image'] = $lastSaison->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // CHAPITRE
        $i++;
        $admin_elements[$i]['element'] = 'chapitre' ;
        $admin_elements[$i]['label'] = 'Chapitres';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'AVENTURE';
        $admin_elements[$i]['nbr'] = $chapitreRepository->countChapitres();
        $lastChapitre = $chapitreRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastChapitre)) {
            $admin_elements[$i]['last'] = $lastChapitre;
            $admin_elements[$i]['nom'] = $lastChapitre->getTitre();
            $admin_elements[$i]['image'] = $lastChapitre->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // EPISODE
        $i++;
        $admin_elements[$i]['element'] = 'episode' ;
        $admin_elements[$i]['label'] = 'Épisodes';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'AVENTURE';
        $admin_elements[$i]['nbr'] = $episodeRepository->countEpisodes();
        $lastEpisode = $episodeRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastEpisode)) {
            $admin_elements[$i]['last'] = $lastEpisode;
            $admin_elements[$i]['nom'] = $lastEpisode->getTitre();
            $admin_elements[$i]['image'] = $lastEpisode->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // SCENE
        $i++;
        $admin_elements[$i]['element'] = 'scene' ;
        $admin_elements[$i]['label'] = 'Scènes';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'AVENTURE';
        $admin_elements[$i]['nbr'] = $sceneRepository->countScenes();
        $lastScene = $sceneRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastScene)) {
            $admin_elements[$i]['last'] = $lastScene;
            $admin_elements[$i]['nom'] = $lastScene->getTitre();
            $admin_elements[$i]['image'] = $lastScene->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // CLAN
        $i++;
        $admin_elements[$i]['element'] = 'clan' ;
        $admin_elements[$i]['label'] = 'Factions';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'EMPIRE';
        $admin_elements[$i]['nbr'] = $clanRepository->countClans();
        $lastClan = $clanRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastClan)) {
            $admin_elements[$i]['last'] = $lastClan;
            $admin_elements[$i]['nom'] = $lastClan->getNom();
            $admin_elements[$i]['image'] = $lastClan->getMon();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // FAMILLE
        $i++;
        $admin_elements[$i]['element'] = 'famille' ;
        $admin_elements[$i]['label'] = 'Familles';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'EMPIRE';
        $admin_elements[$i]['nbr'] = $familleRepository->countFamilles();
        $lastFamille = $familleRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastFamille)) {
            $admin_elements[$i]['last'] = $lastFamille;
            $admin_elements[$i]['nom'] = $lastFamille->getNom();
            $admin_elements[$i]['image'] = $lastFamille->getMon();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }
        
        // ARCHIVE
        $i++;
        $admin_elements[$i]['element'] = 'archive' ;
        $admin_elements[$i]['label'] = 'Archives';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'EMPIRE';
        $admin_elements[$i]['nbr'] = $archiveRepository->countArchives();
        $lastArchive = $archiveRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastArchive)) {
            $admin_elements[$i]['last'] = $lastArchive;
            $admin_elements[$i]['nom'] = $lastArchive->getTitre();
            $admin_elements[$i]['image'] = $lastArchive->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // LIEU
        $i++;
        $admin_elements[$i]['element'] = 'lieu' ;
        $admin_elements[$i]['label'] = 'Lieux';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'EMPIRE';
        $admin_elements[$i]['nbr'] = $lieuRepository->countLieux();
        $lastLieu = $lieuRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastLieu)) {
            $admin_elements[$i]['last'] = $lastLieu;
            $admin_elements[$i]['nom'] = $lastLieu->getNom();
            $admin_elements[$i]['image'] = $lastLieu->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // LORE
        $i++;
        $admin_elements[$i]['element'] = 'lore' ;
        $admin_elements[$i]['label'] = 'Lores';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'EMPIRE';
        $admin_elements[$i]['nbr'] = $loreRepository->countLores();
        $lastLore = $loreRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastLore)) {
            $admin_elements[$i]['last'] = $lastLore;
            $admin_elements[$i]['nom'] = $lastLore->getNom();
            $admin_elements[$i]['image'] = $lastLore->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // PERSONNAGE
        $i++;
        $admin_elements[$i]['element'] = 'personnage' ;
        $admin_elements[$i]['label'] = 'Personnages';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'PERSONNAGES';
        $admin_elements[$i]['nbr'] = $personnageRepository->countPJs();
        $lastPersonnage = $personnageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastPersonnage)) {
            $admin_elements[$i]['last'] = $lastPersonnage;
            $admin_elements[$i]['nom'] = $lastPersonnage->getNom();
            $admin_elements[$i]['image'] = $lastPersonnage->getIcone();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // FICHE
        $i++;
        $admin_elements[$i]['element'] = 'fiche' ;
        $admin_elements[$i]['label'] = 'Fiches';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'PERSONNAGES';
        $admin_elements[$i]['nbr'] = $fichePersonnageRepository->countFiches();
        $lastFiche = $fichePersonnageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastFiche)) {
            $admin_elements[$i]['last'] = $lastFiche;
            $admin_elements[$i]['nom'] = $lastFiche->getPersonnage()->getNom();
            $admin_elements[$i]['image'] = $lastFiche->getPersonnage()->getIcone();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // REGLES
        $i++;
        $admin_elements[$i]['element'] = 'rule' ;
        $admin_elements[$i]['label'] = 'Règles';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $ruleRepository->countRules();
        $lastRule = $ruleRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastRule)) {
            $admin_elements[$i]['last'] = $lastRule;
            $admin_elements[$i]['nom'] = $lastRule->getNom();
            $admin_elements[$i]['image'] = $lastRule->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // LIBRARY
        $i++;
        $admin_elements[$i]['element'] = 'library' ;
        $admin_elements[$i]['label'] = 'Bibliothèques';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $libraryRepository->countLibraries();
        $lastLibrary = $libraryRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastLibrary)) {
            $admin_elements[$i]['last'] = $lastLibrary;
            $admin_elements[$i]['nom'] = $lastLibrary->getNom();
            $admin_elements[$i]['image'] = $lastLibrary->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // CLASSE
        $i++;
        $admin_elements[$i]['element'] = 'classe' ;
        $admin_elements[$i]['label'] = 'Classes';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $classeRepository->countClasses();
        $lastClasse = $classeRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastClasse)) {
            $admin_elements[$i]['last'] = $lastClasse;
            $admin_elements[$i]['nom'] = $lastClasse->getNom();
            $admin_elements[$i]['image'] = $lastClasse->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // ECOLE
        $i++;
        $admin_elements[$i]['element'] = 'ecole' ;
        $admin_elements[$i]['label'] = 'Écoles';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $ecoleRepository->countEcoles();
        $lastEcole = $ecoleRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastEcole)) {
            $admin_elements[$i]['last'] = $lastEcole;
            $admin_elements[$i]['nom'] = $lastEcole->getNom();
            $admin_elements[$i]['image'] = $lastEcole->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // AVANTAGE
        $i++;
        $admin_elements[$i]['element'] = 'avantage' ;
        $admin_elements[$i]['label'] = 'Avantages/Dés.';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $avantageRepository->countAvantages();
        $lastAvantage = $avantageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastAvantage)) {
            $admin_elements[$i]['last'] = $lastAvantage;
            $admin_elements[$i]['nom'] = $lastAvantage->getNom();
            $admin_elements[$i]['image'] = '';
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // COMPETENCE
        $i++;
        $admin_elements[$i]['element'] = 'competence' ;
        $admin_elements[$i]['label'] = 'Compétences';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $competenceRepository->countCompetences();
        $lastCompetence = $competenceRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastCompetence)) {
            $admin_elements[$i]['last'] = $lastCompetence;
            $admin_elements[$i]['nom'] = $lastCompetence->getNom();
            $admin_elements[$i]['image'] = '';
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // OBJET
        $i++;
        $admin_elements[$i]['element'] = 'objet' ;
        $admin_elements[$i]['label'] = 'Objets';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $objetRepository->countObjets();
        $lastObjet = $objetRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastObjet)) {
            $admin_elements[$i]['last'] = $lastObjet;
            $admin_elements[$i]['nom'] = $lastObjet->getNom();
            $admin_elements[$i]['image'] = $lastObjet->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        // SORT
        $i++;
        $admin_elements[$i]['element'] = 'sort' ;
        $admin_elements[$i]['label'] = 'Sorts';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'REGLES';
        $admin_elements[$i]['nbr'] = $sortRepository->countSorts();
        $lastSort = $sortRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSort)) {
            $admin_elements[$i]['last'] = $lastSort;
            $admin_elements[$i]['nom'] = $lastSort->getNom();
            $admin_elements[$i]['image'] = '';
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
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