<?php

namespace App\Controller;

use App\Service\ClasseurXP;
use App\Service\CurrentPlayer;
use App\Service\Localisateur;
use App\Service\NewsFeed;
use App\Service\Visibility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private const VIGNETTES = 4;
    private const VIGNETTES_PORTRAITS = 8;

    private const PARTICIPATIONS = 2;

    private const SEANCES_PRECEDENTES = 3;

    private const MARGE_DECOUVERTES = 2;

    private const DECOUVERTES = [
        ['titre' => 'Personnages', 'vide' => 'Aucun personnage découvert', 'variant' => 'portrait', 'cle' => 'personnage', 'max' => 4],
        ['titre' => 'Lieux', 'vide' => 'Aucun lieu découvert', 'variant' => 'icon', 'cle' => 'lieu', 'max' => 3],
        ['titre' => 'Objets', 'vide' => 'Aucun objet découvert', 'variant' => 'rule', 'field' => 'icon', 'cle' => 'objet', 'max' => 3],
    ];

    private const GROUPES = [
        ['titre' => 'Personnages Joueurs', 'variant' => 'portrait', 'cles' => ['personnage'], 'criteres' => ['estPj' => true]],
        ['titre' => 'Personnages Non Joueurs', 'variant' => 'portrait', 'cles' => ['personnage'], 'criteres' => ['estPj' => false]],
        ['titre' => 'Lieux', 'variant' => 'icon', 'cles' => ['lieu']],
        ['titre' => 'Objets & Equipements', 'variant' => 'rule', 'field' => 'icon', 'sections' => [
            ['titre' => 'Divers', 'cles' => ['objet'], 'criteres' => ['categorie' => 'OBJET'], 'max' => 2],
            ['titre' => 'Armes & Armures', 'cles' => ['objet'], 'criteres' => ['categorie' => ['ARME', 'ARMURE', 'PROJECTILE']], 'max' => 2],
        ]],
        ['titre' => 'Factions', 'variant' => 'emblem', 'cles' => ['clan']],
        ['titre' => 'Écoles', 'variant' => 'rule', 'cles' => ['ecole']],
        ['titre' => 'Archives et Lore', 'sections' => [
            ['titre' => 'Archives', 'cles' => ['archive'], 'max' => 2],
            ['titre' => 'Lore', 'cles' => ['lore'], 'max' => 2],
        ]],
        ['titre' => 'Règles', 'variant' => 'rule', 'cles' => ['rule']],
        ['titre' => 'Bibliothèques', 'variant' => 'rule', 'large' => true, 'sections' => [
            ['titre' => 'Avantages & Désavantages', 'cles' => ['avantage'], 'max' => 2],
            ['titre' => 'Compétences', 'cles' => ['competence'], 'max' => 2],
            ['titre' => 'Sorts', 'cles' => ['sort'], 'max' => 4],
        ]],
    ];

    /**
     * @Route("/", name="news")
     */
    public function index(NewsFeed $newsFeed, ClasseurXP $classeurXP, CurrentPlayer $currentPlayer, Visibility $visibility, Localisateur $localisateur): Response
    {
        $mesPersonnages = $currentPlayer->characterIds();
        $monPersonnage = $currentPlayer->navigatingCharacter();

        $participations = $newsFeed->lastParticipations(self::PARTICIPATIONS);
        $carte = $localisateur->carte($participations === [] ? null : $monPersonnage, true);
        $episodes = $newsFeed->lastEpisodes(1 + self::SEANCES_PRECEDENTES);
        $dernierEpisode = $episodes[0] ?? null;

        $participationsXp = [];
        foreach ($participations as $participation) {
            $participationsXp[$participation->getId()] = $classeurXP->cumulUnPersoEpisode(
                $participation->getScene()->getEpisodeParent(),
                $participation->getPersonnage()->getId()
            );
        }

        $sections = [
            ['entity' => 'story', 'titleLight' => '', 'titleStrong' => 'Dernière Séance'],
            ['entity' => 'news', 'titleLight' => '', 'titleStrong' => 'Nouveautés'],
        ];

        $groupes = [];
        $trouver = function (array $cles, int $max, array $criteres = []) use ($newsFeed, $visibility) {
            return array_slice($visibility->listedOnly($newsFeed->latestAmong($cles, $max, $criteres)), 0, $max);
        };

        foreach (self::GROUPES as $groupe) {
            if (isset($groupe['sections'])) {
                $sectionsDuGroupe = [];
                foreach ($groupe['sections'] as $section) {
                    $elements = $trouver($section['cles'], $section['max'], $section['criteres'] ?? []);

                    if (!empty($elements)) {
                        $sectionsDuGroupe[] = $section + ['elements' => $elements];
                    }
                }

                if (!empty($sectionsDuGroupe)) {
                    $groupes[] = ['sections' => $sectionsDuGroupe] + $groupe;
                }

                continue;
            }

            $portrait = ($groupe['variant'] ?? null) === 'portrait';
            $max = $portrait ? self::VIGNETTES_PORTRAITS : self::VIGNETTES;
            $elements = $trouver($groupe['cles'], $max, $groupe['criteres'] ?? []);

            if (!empty($elements)) {
                $groupes[] = $groupe + [
                    'perRow' => ['desktop' => $portrait ? $max / 2 : $max, 'tablet' => $max / 2],
                    'elements' => $elements,
                ];
            }
        }

        $colonne = 0;
        foreach ($groupes as $groupe) {
            $colonne = ($groupe['large'] ?? false) ? 0 : 1 - $colonne;
        }

        $monRecap = null;
        $decouvertes = [];
        if ($monPersonnage !== null) {
            $monRecap = [
                'pvMax' => $monPersonnage->getFichePersonnage()?->getPvMax(),
                'rang' => $classeurXP->rank($classeurXP->total($monPersonnage)),
            ];

            foreach (self::DECOUVERTES as $decouverte) {
                $elements = $newsFeed->lastDiscovered($decouverte['cle'], $decouverte['max'] * self::MARGE_DECOUVERTES);
                $decouvertes[] = $decouverte + ['elements' => array_slice($visibility->listedOnly($elements), 0, $decouverte['max'])];
            }
        }

        return $this->render('news/index.html.twig', [
            'dernier_episode' => $dernierEpisode,
            'classement' => $dernierEpisode === null ? false : $classeurXP->classerPersosEpisode($dernierEpisode),
            'session_locations' => $dernierEpisode === null ? [] : $newsFeed->episodeLocations($dernierEpisode),
            'session_characters' => $dernierEpisode === null ? [] : $newsFeed->episodeCharacters($dernierEpisode),
            'session_objects' => $dernierEpisode === null ? [] : $newsFeed->episodeObjects($dernierEpisode),
            'session_previous' => array_slice($episodes, 1),
            'mes_personnages' => $mesPersonnages,
            'mon_personnage' => $monPersonnage,
            'mon_recap' => $monRecap,
            'mes_participations' => $participations,
            'classement_general' => $monPersonnage === null ? $newsFeed->generalRanking() : [],
            'decouvertes' => $decouvertes,
            'participations_xp' => $participationsXp,
            'groupes' => $groupes,
            'secrets_large' => $colonne === 0,
            'sections' => $sections,
            'header_classname' => 'news',
            'header_up' => 'Accueil',
            'header_down' => 'Bienvenue à Rokugan',
            'category' => 'news',
        ] + $carte);
    }
}
