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
use App\Repository\DevelopmentRepository;
use App\Repository\PersonnageRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\FichePersonnageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BackOfficeController extends AbstractController
{
    /**
     * @Route("/back-office", name="back_office")
     * @Security("is_granted('ROLE_MJ') or is_granted('ROLE_ADMIN')")
     */
    public function index(
        SaisonRepository $seasonRepository,
        ChapitreRepository $chapterRepository,
        EpisodeRepository $episodeRepository,
        SceneRepository $sceneRepository,

        ClanRepository $clanRepository,
        FamilleRepository $familyRepository,
        ArchiveRepository $archiveRepository,
        LieuRepository $locationRepository,
        LoreRepository $loreRepository,

        RuleRepository $ruleRepository,
        LibraryRepository $libraryRepository,
        ClasseRepository $classRepository,
        EcoleRepository $dojoRepository,
        AvantageRepository $advantageRepository,
        CompetenceRepository $skillRepository,
        ObjetRepository $objectRepository,
        SortRepository $spellRepository,

        PersonnageRepository $characterRepository,
        FichePersonnageRepository $sheetRepository,
        DevelopmentRepository $developmentRepository,

        UtilisateurRepository $userRepository ): Response
    {
        $admin_elements = [];
        $i = 0;

        $admin_elements[$i]['element'] = 'season' ;
        $admin_elements[$i]['label'] = 'Saisons';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Aventure';
        $admin_elements[$i]['nbr'] = $seasonRepository->countSaisons();
        $lastSeason = $seasonRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSeason)) {
            $admin_elements[$i]['last'] = $lastSeason;
            $admin_elements[$i]['nom'] = $lastSeason->getTitre();
            $admin_elements[$i]['image'] = $lastSeason->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'chapter' ;
        $admin_elements[$i]['label'] = 'Chapitres';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Aventure';
        $admin_elements[$i]['nbr'] = $chapterRepository->countChapitres();
        $lastChapter = $chapterRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastChapter)) {
            $admin_elements[$i]['last'] = $lastChapter;
            $admin_elements[$i]['nom'] = $lastChapter->getTitre();
            $admin_elements[$i]['image'] = $lastChapter->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'episode' ;
        $admin_elements[$i]['label'] = 'Épisodes';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Aventure';
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

        $i++;
        $admin_elements[$i]['element'] = 'scene' ;
        $admin_elements[$i]['label'] = 'Scènes';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Aventure';
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

        $i++;
        $admin_elements[$i]['element'] = 'clan' ;
        $admin_elements[$i]['label'] = 'Factions';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Univers';
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

        $i++;
        $admin_elements[$i]['element'] = 'family' ;
        $admin_elements[$i]['label'] = 'Familles';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Univers';
        $admin_elements[$i]['nbr'] = $familyRepository->countFamilles();
        $lastFamily = $familyRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastFamily)) {
            $admin_elements[$i]['last'] = $lastFamily;
            $admin_elements[$i]['nom'] = $lastFamily->getNom();
            $admin_elements[$i]['image'] = $lastFamily->getMon();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }
        
        $i++;
        $admin_elements[$i]['element'] = 'archive' ;
        $admin_elements[$i]['label'] = 'Archives';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Univers';
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

        $i++;
        $admin_elements[$i]['element'] = 'location' ;
        $admin_elements[$i]['label'] = 'Lieux';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Univers';
        $admin_elements[$i]['nbr'] = $locationRepository->countLieux();
        $lastLocation = $locationRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastLocation)) {
            $admin_elements[$i]['last'] = $lastLocation;
            $admin_elements[$i]['nom'] = $lastLocation->getNom();
            $admin_elements[$i]['image'] = $lastLocation->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'lore' ;
        $admin_elements[$i]['label'] = 'Lores';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Univers';
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

        $i++;
        $admin_elements[$i]['element'] = 'character' ;
        $admin_elements[$i]['label'] = 'Personnages';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Personnages';
        $admin_elements[$i]['nbr'] = $characterRepository->countPersonnages();
        $lastCharacter = $characterRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastCharacter)) {
            $admin_elements[$i]['last'] = $lastCharacter;
            $admin_elements[$i]['nom'] = $lastCharacter->getNomComplet();
            $admin_elements[$i]['image'] = $lastCharacter->getIcone();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'sheet' ;
        $admin_elements[$i]['label'] = 'Fiches';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Personnages';
        $admin_elements[$i]['nbr'] = $sheetRepository->countFiches();
        $lastSheet = $sheetRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSheet)) {
            $admin_elements[$i]['last'] = $lastSheet;
            $admin_elements[$i]['nom'] = $lastSheet->getPersonnage()->getNomComplet();
            $admin_elements[$i]['image'] = $lastSheet->getPersonnage()->getIcone();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'development' ;
        $admin_elements[$i]['label'] = 'Développements';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Personnages';
        $admin_elements[$i]['nbr'] = count($developmentRepository->findAll());
        $lastDevelopment = $developmentRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastDevelopment)) {
            $admin_elements[$i]['last'] = $lastDevelopment;
            $admin_elements[$i]['nom'] = $lastDevelopment->getPersonnage()->getPrenom();
            $admin_elements[$i]['image'] = $lastDevelopment->getPersonnage()->getIllustration();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'rule' ;
        $admin_elements[$i]['label'] = 'Règles';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Règles';
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

        $i++;
        $admin_elements[$i]['element'] = 'library' ;
        $admin_elements[$i]['label'] = 'Bibliothèques';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Règles';
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

        $i++;
        $admin_elements[$i]['element'] = 'class' ;
        $admin_elements[$i]['label'] = 'Classes';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Règles';
        $admin_elements[$i]['nbr'] = $classRepository->countClasses();
        $lastClass = $classRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastClass)) {
            $admin_elements[$i]['last'] = $lastClass;
            $admin_elements[$i]['nom'] = $lastClass->getNom();
            $admin_elements[$i]['image'] = $lastClass->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'dojo' ;
        $admin_elements[$i]['label'] = 'Écoles';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Règles';
        $admin_elements[$i]['nbr'] = $dojoRepository->countEcoles();
        $lastDojo = $dojoRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastDojo)) {
            $admin_elements[$i]['last'] = $lastDojo;
            $admin_elements[$i]['nom'] = $lastDojo->getNom();
            $admin_elements[$i]['image'] = $lastDojo->getImage();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'advantage' ;
        $admin_elements[$i]['label'] = 'Avantages / Dés.';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Règles';
        $admin_elements[$i]['nbr'] = $advantageRepository->countAvantages();
        $lastAdvantage = $advantageRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastAdvantage)) {
            $admin_elements[$i]['last'] = $lastAdvantage;
            $admin_elements[$i]['nom'] = $lastAdvantage->getNom();
            $admin_elements[$i]['image'] = '';
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'skill' ;
        $admin_elements[$i]['label'] = 'Compétences';
        $admin_elements[$i]['genre'] = 'F';
        $admin_elements[$i]['categorie'] = 'Règles';
        $admin_elements[$i]['nbr'] = $skillRepository->countCompetences();
        $lastSkill = $skillRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSkill)) {
            $admin_elements[$i]['last'] = $lastSkill;
            $admin_elements[$i]['nom'] = $lastSkill->getNom();
            $admin_elements[$i]['image'] = '';
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'object' ;
        $admin_elements[$i]['label'] = 'Objets';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Règles';
        $admin_elements[$i]['nbr'] = $objectRepository->countObjets();
        $lastObject = $objectRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastObject)) {
            $admin_elements[$i]['last'] = $lastObject;
            $admin_elements[$i]['nom'] = $lastObject->getNom();
            $admin_elements[$i]['image'] = $lastObject->getIcon();
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $i++;
        $admin_elements[$i]['element'] = 'spell' ;
        $admin_elements[$i]['label'] = 'Sorts';
        $admin_elements[$i]['genre'] = 'M';
        $admin_elements[$i]['categorie'] = 'Règles';
        $admin_elements[$i]['nbr'] = $spellRepository->countSorts();
        $lastSpell = $spellRepository->findOneBy(array(),array('id' => 'DESC'));
        if (!empty($lastSpell)) {
            $admin_elements[$i]['last'] = $lastSpell;
            $admin_elements[$i]['nom'] = $lastSpell->getNom();
            $admin_elements[$i]['image'] = '';
        } else {
            $admin_elements[$i]['last'] = '';
            $admin_elements[$i]['nom'] = '';
            $admin_elements[$i]['image'] = '';
        }

        $userCount = $userRepository->countUtilisateurs();
        $lastUser = $userRepository->findOneBy(array(),array('id' => 'DESC'));

        return $this->render('back_office/index.html.twig', [
            'nbrUsers' => $userCount,
            'lastUser' => $lastUser,
            'admin_elements' => $admin_elements,
        ]);
    }
}