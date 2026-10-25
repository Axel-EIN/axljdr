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
        ['title' => 'Personnages', 'empty' => 'Aucun personnage découvert', 'variant' => 'portrait', 'key' => 'character', 'max' => 4],
        ['title' => 'Lieux', 'empty' => 'Aucun lieu découvert', 'variant' => 'icon', 'key' => 'location', 'max' => 3],
        ['title' => 'Objets', 'empty' => 'Aucun objet découvert', 'variant' => 'rule', 'field' => 'icon', 'key' => 'object', 'max' => 3],
    ];

    private const GROUPS = [
        ['title' => 'Personnages Joueurs', 'variant' => 'portrait', 'keys' => ['character'], 'criteria' => ['estPj' => true]],
        ['title' => 'Personnages Non Joueurs', 'variant' => 'portrait', 'keys' => ['character'], 'criteria' => ['estPj' => false]],
        ['title' => 'Lieux', 'variant' => 'icon', 'keys' => ['location']],
        ['title' => 'Objets & Equipements', 'variant' => 'rule', 'field' => 'icon', 'sections' => [
            ['title' => 'Divers', 'keys' => ['object'], 'criteria' => ['categorie' => 'OBJET'], 'max' => 2],
            ['title' => 'Armes & Armures', 'keys' => ['object'], 'criteria' => ['categorie' => ['ARME', 'ARMURE', 'PROJECTILE']], 'max' => 2],
        ]],
        ['title' => 'Factions', 'variant' => 'emblem', 'keys' => ['clan']],
        ['title' => 'Écoles', 'variant' => 'rule', 'keys' => ['dojo']],
        ['title' => 'Archives et Lore', 'sections' => [
            ['title' => 'Archives', 'keys' => ['archive'], 'max' => 2],
            ['title' => 'Lore', 'keys' => ['lore'], 'max' => 2],
        ]],
        ['title' => 'Règles', 'variant' => 'rule', 'keys' => ['rule']],
        ['title' => 'Bibliothèques', 'variant' => 'rule', 'large' => true, 'sections' => [
            ['title' => 'Avantages & Désavantages', 'keys' => ['advantage'], 'max' => 2],
            ['title' => 'Compétences', 'keys' => ['skill'], 'max' => 2],
            ['title' => 'Sorts', 'keys' => ['spell'], 'max' => 4],
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
                    $elements = $find($section['keys'], $section['max'], $section['criteria'] ?? []);

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
            $elements = $find($group['keys'], $max, $group['criteria'] ?? []);

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
                'maxHp' => $myCharacter->getFichePersonnage()?->getPvMax(),
                'rank' => $xpRanker->rank($xpRanker->total($myCharacter)),
            ];

            foreach (self::DISCOVERIES as $discovery) {
                $elements = $newsFeed->lastDiscovered($discovery['key'], $discovery['max'] * self::DISCOVERY_MARGIN);
                $discoveries[] = $discovery + ['elements' => array_slice($visibility->listedOnly($elements), 0, $discovery['max'])];
            }
        }

        return $this->render('news/index.html.twig', [
            'last_episode' => $lastEpisode,
            'ranking' => $lastEpisode === null ? false : $xpRanker->rankEpisodeCharacters($lastEpisode),
            'session_locations' => $lastEpisode === null ? [] : $newsFeed->episodeLocations($lastEpisode),
            'session_characters' => $lastEpisode === null ? [] : $newsFeed->episodeCharacters($lastEpisode),
            'session_objects' => $lastEpisode === null ? [] : $newsFeed->episodeObjects($lastEpisode),
            'session_previous' => array_slice($episodes, 1),
            'my_characters' => $myCharacters,
            'my_character' => $myCharacter,
            'my_summary' => $mySummary,
            'my_participations' => $participations,
            'general_ranking' => $myCharacter === null ? $newsFeed->generalRanking() : [],
            'discoveries' => $discoveries,
            'participations_xp' => $participationsXp,
            'groups' => $groups,
            'secrets_large' => $column === 0,
            'sections' => $sections,
            'header_classname' => 'news',
            'header_up' => 'Accueil',
            'header_down' => 'Bienvenue à Rokugan',
            'category' => 'news',
        ] + $map);
    }
}
