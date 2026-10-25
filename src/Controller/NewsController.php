<?php

namespace App\Controller;

use App\Service\XpRanker;
use App\Service\CurrentPlayer;
use App\Service\Locator;
use App\Service\NewsFeed;
use App\Service\Visibility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private const THUMBNAILS = 4;
    private const PORTRAIT_THUMBNAILS = 8;

    private const PARTICIPATIONS = 2;

    private const PREVIOUS_SESSIONS = 3;

    private const DISCOVERY_MARGIN = 2;

    private const DISCOVERIES = [
        ['titre' => 'Personnages', 'vide' => 'Aucun personnage découvert', 'variant' => 'portrait', 'cle' => 'character', 'max' => 4],
        ['titre' => 'Lieux', 'vide' => 'Aucun lieu découvert', 'variant' => 'icon', 'cle' => 'location', 'max' => 3],
        ['titre' => 'Objets', 'vide' => 'Aucun objet découvert', 'variant' => 'rule', 'field' => 'icon', 'cle' => 'object', 'max' => 3],
    ];

    private const GROUPS = [
        ['titre' => 'Personnages Joueurs', 'variant' => 'portrait', 'cles' => ['character'], 'criteres' => ['estPj' => true]],
        ['titre' => 'Personnages Non Joueurs', 'variant' => 'portrait', 'cles' => ['character'], 'criteres' => ['estPj' => false]],
        ['titre' => 'Lieux', 'variant' => 'icon', 'cles' => ['location']],
        ['titre' => 'Objets & Equipements', 'variant' => 'rule', 'field' => 'icon', 'sections' => [
            ['titre' => 'Divers', 'cles' => ['object'], 'criteres' => ['categorie' => 'OBJET'], 'max' => 2],
            ['titre' => 'Armes & Armures', 'cles' => ['object'], 'criteres' => ['categorie' => ['ARME', 'ARMURE', 'PROJECTILE']], 'max' => 2],
        ]],
        ['titre' => 'Factions', 'variant' => 'emblem', 'cles' => ['clan']],
        ['titre' => 'Écoles', 'variant' => 'rule', 'cles' => ['dojo']],
        ['titre' => 'Archives et Lore', 'sections' => [
            ['titre' => 'Archives', 'cles' => ['archive'], 'max' => 2],
            ['titre' => 'Lore', 'cles' => ['lore'], 'max' => 2],
        ]],
        ['titre' => 'Règles', 'variant' => 'rule', 'cles' => ['rule']],
        ['titre' => 'Bibliothèques', 'variant' => 'rule', 'large' => true, 'sections' => [
            ['titre' => 'Avantages & Désavantages', 'cles' => ['advantage'], 'max' => 2],
            ['titre' => 'Compétences', 'cles' => ['skill'], 'max' => 2],
            ['titre' => 'Sorts', 'cles' => ['spell'], 'max' => 4],
        ]],
    ];

    /**
     * @Route("/", name="news")
     */
    public function index(NewsFeed $newsFeed, XpRanker $xpRanker, CurrentPlayer $currentPlayer, Visibility $visibility, Locator $locator): Response
    {
        $myCharacters = $currentPlayer->characterIds();
        $myCharacter = $currentPlayer->navigatingCharacter();

        $participations = $newsFeed->lastParticipations(self::PARTICIPATIONS);
        $map = $locator->map($participations === [] ? null : $myCharacter, true);
        $episodes = $newsFeed->lastEpisodes(1 + self::PREVIOUS_SESSIONS);
        $lastEpisode = $episodes[0] ?? null;

        $participationsXp = [];
        foreach ($participations as $participation) {
            $participationsXp[$participation->getId()] = $xpRanker->characterEpisodeTotal(
                $participation->getScene()->getEpisodeParent(),
                $participation->getPersonnage()->getId()
            );
        }

        $sections = [
            ['entity' => 'story', 'titleLight' => '', 'titleStrong' => 'Dernière Séance'],
            ['entity' => 'news', 'titleLight' => '', 'titleStrong' => 'Nouveautés'],
        ];

        $groups = [];
        $find = function (array $keys, int $max, array $criteria = []) use ($newsFeed, $visibility) {
            return array_slice($visibility->listedOnly($newsFeed->latestAmong($keys, $max, $criteria)), 0, $max);
        };

        foreach (self::GROUPS as $group) {
            if (isset($group['sections'])) {
                $groupSections = [];
                foreach ($group['sections'] as $section) {
                    $elements = $find($section['cles'], $section['max'], $section['criteres'] ?? []);

                    if (!empty($elements)) {
                        $groupSections[] = $section + ['elements' => $elements];
                    }
                }

                if (!empty($groupSections)) {
                    $groups[] = ['sections' => $groupSections] + $group;
                }

                continue;
            }

            $portrait = ($group['variant'] ?? null) === 'portrait';
            $max = $portrait ? self::PORTRAIT_THUMBNAILS : self::THUMBNAILS;
            $elements = $find($group['cles'], $max, $group['criteres'] ?? []);

            if (!empty($elements)) {
                $groups[] = $group + [
                    'perRow' => ['desktop' => $portrait ? $max / 2 : $max, 'tablet' => $max / 2],
                    'elements' => $elements,
                ];
            }
        }

        $column = 0;
        foreach ($groups as $group) {
            $column = ($group['large'] ?? false) ? 0 : 1 - $column;
        }

        $mySummary = null;
        $discoveries = [];
        if ($myCharacter !== null) {
            $mySummary = [
                'pvMax' => $myCharacter->getFichePersonnage()?->getPvMax(),
                'rang' => $xpRanker->rank($xpRanker->total($myCharacter)),
            ];

            foreach (self::DISCOVERIES as $discovery) {
                $elements = $newsFeed->lastDiscovered($discovery['cle'], $discovery['max'] * self::DISCOVERY_MARGIN);
                $discoveries[] = $discovery + ['elements' => array_slice($visibility->listedOnly($elements), 0, $discovery['max'])];
            }
        }

        return $this->render('news/index.html.twig', [
            'dernier_episode' => $lastEpisode,
            'classement' => $lastEpisode === null ? false : $xpRanker->rankEpisodeCharacters($lastEpisode),
            'session_locations' => $lastEpisode === null ? [] : $newsFeed->episodeLocations($lastEpisode),
            'session_characters' => $lastEpisode === null ? [] : $newsFeed->episodeCharacters($lastEpisode),
            'session_objects' => $lastEpisode === null ? [] : $newsFeed->episodeObjects($lastEpisode),
            'session_previous' => array_slice($episodes, 1),
            'mes_personnages' => $myCharacters,
            'mon_personnage' => $myCharacter,
            'mon_recap' => $mySummary,
            'mes_participations' => $participations,
            'classement_general' => $myCharacter === null ? $newsFeed->generalRanking() : [],
            'decouvertes' => $discoveries,
            'participations_xp' => $participationsXp,
            'groupes' => $groups,
            'secrets_large' => $column === 0,
            'sections' => $sections,
            'header_classname' => 'news',
            'header_up' => 'Accueil',
            'header_down' => 'Bienvenue à Rokugan',
            'category' => 'news',
        ] + $map);
    }
}
