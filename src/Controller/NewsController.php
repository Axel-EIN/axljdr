<?php

namespace App\Controller;

use App\Repository\LibraryRepository;
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
        ['title' => 'Personnages', 'icon' => 'fa-users', 'image' => '/assets/rubriques/characters.jpg', 'variant' => 'portrait', 'large' => true, 'gap' => '1rem',
            'link' => ['label' => 'Tous les personnages', 'route' => 'characters'], 'characters' => true],
        ['title' => 'Lieux', 'icon' => 'fa-vihara', 'image' => '/assets/rubriques/world.jpg', 'variant' => 'icon', 'large' => true, 'max' => 8, 'gap' => '1rem',
            'link' => ['label' => 'Tous les lieux', 'route' => 'world', 'params' => ['_fragment' => 'location']], 'keys' => ['location']],
        ['title' => 'Factions', 'icon' => 'fa-flag', 'library_image' => 'object', 'variant' => 'rule', 'large' => true, 'gap' => '1rem',
            'link' => ['segments' => [
                'Tous les :',
                ['label' => 'Factions', 'route' => 'world', 'params' => ['_fragment' => 'clan']],
                '|',
                ['label' => 'Écoles', 'route' => 'rules', 'params' => ['_fragment' => 'class']],
            ]],
            'sections' => [
            'factions' => ['max' => 4, 'variant' => 'emblem', 'keys' => ['clan']],
            'schools' => ['max' => 8, 'share' => 'factions', 'keys' => ['dojo']],
        ]],
        ['title' => 'Articles', 'icon' => 'fa-readme', 'icon_style' => 'fa-brands', 'image' => '/assets/rubriques/rules.jpg', 'variant' => 'rule', 'large' => true, 'gap' => '1rem',
            'link' => ['segments' => [
                'Tous les :',
                ['label' => 'Lore', 'route' => 'world', 'params' => ['_fragment' => 'lore']],
                '|',
                ['label' => 'Règles', 'route' => 'rules', 'params' => ['_fragment' => 'rule']],
            ]],
            'sections' => [
            'world' => ['max' => 4, 'pools' => [
                'lore' => ['keys' => ['lore'], 'quota' => 2],
                'archive' => ['keys' => ['archive'], 'quota' => 2],
            ]],
            'rules' => ['max' => 8, 'share' => 'world', 'pools' => [
                'base' => ['keys' => ['rule'], 'criteria' => ['base' => true], 'quota' => 2],
                'annex' => ['keys' => ['rule'], 'criteria' => ['base' => false], 'quota' => 2],
            ]],
        ]],
        ['title' => 'Bibliothèques', 'icon' => 'fa-book', 'library_image' => 'spell', 'variant' => 'rule', 'large' => true,
            'link' => ['label' => 'Toutes les bibliothèques', 'route' => 'rules', 'params' => ['_fragment' => 'library']],
            'sections' => [
            'spells' => ['max' => 4, 'pools' => [
                'magic' => ['keys' => ['spell'], 'criteria' => ['categorie' => 'MAGIE'], 'quota' => 1],
                'kiho' => ['keys' => ['spell'], 'criteria' => ['categorie' => 'KIHO'], 'quota' => 1],
                'tattoo' => ['keys' => ['spell'], 'criteria' => ['categorie' => 'TATOUAGE'], 'quota' => 1],
                'maho' => ['keys' => ['spell'], 'criteria' => ['categorie' => 'MAHO'], 'quota' => 1],
            ]],
            'traits' => ['max' => 8, 'share' => 'spells', 'pools' => [
                'skill' => ['keys' => ['skill'], 'quota' => 1],
                'advantage' => ['keys' => ['advantage'], 'criteria' => ['genre' => 'Avantage'], 'quota' => 1],
                'disadvantage' => ['keys' => ['advantage'], 'criteria' => ['genre' => 'Désavantage'], 'quota' => 1],
                'object' => ['keys' => ['object'], 'quota' => 1],
            ]],
        ]],
    ];

    /**
     * @Route("/", name="news")
     */
    public function index(NewsFeed $newsFeed, XpRanker $xpRanker, CurrentPlayer $currentPlayer, Visibility $visibility, Locator $locator, LibraryRepository $libraryRepository): Response
    {
        $myCharacters = $currentPlayer->characterIds();
        $myCharacter = $currentPlayer->navigatingCharacter();

        $participations = $newsFeed->lastParticipations(self::PARTICIPATIONS);
        $map = $locator->map($participations === [] ? null : $myCharacter, true);
        $episodes = $newsFeed->lastEpisodes(1 + self::PREVIOUS_SESSIONS);
        $lastEpisode = $episodes[0] ?? null;
        $previousEpisodes = array_slice($episodes, 1);

        $participationsXp = [];
        foreach ($participations as $participation) {
            $participationsXp[$participation->getId()] = $xpRanker->characterEpisodeTotal(
                $participation->getScene()->getEpisodeParent(),
                $participation->getPersonnage()->getId()
            );
        }

        $sections = [
            ['entity' => 'story', 'titleLight' => '', 'titleStrong' => 'Dernière Séance'],
            ['entity' => 'news', 'titleLight' => '', 'titleStrong' => "Quoi de neuf\u{00A0}?"],
        ];

        $groups = $this->groups($newsFeed, $libraryRepository);

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
            'session_previous' => $previousEpisodes,
            'previous_players' => array_combine(
                array_map(fn ($episode) => $episode->getId(), $previousEpisodes),
                array_map(fn ($episode) => $xpRanker->rankEpisodeCharacters($episode) ?: [], $previousEpisodes)
            ),
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

    private function groups(NewsFeed $newsFeed, LibraryRepository $libraryRepository): array
    {
        $groups = [];

        foreach (self::GROUPS as $group) {
            if (isset($group['library_image'])) {
                $group['image'] = $libraryRepository->findOneBy(['entity' => $group['library_image'], 'base' => true])?->getImage();
            }

            if (isset($group['sections'])) {
                $sections = $this->groupSections($newsFeed, $group['sections']);

                if (!empty($sections)) {
                    $groups[] = ['sections' => $sections] + $group;
                }

                continue;
            }

            $portrait = ($group['variant'] ?? null) === 'portrait';
            $max = $group['max'] ?? ($portrait ? self::PORTRAIT_THUMBNAILS : self::THUMBNAILS);
            $characters = $group['characters'] ?? false;
            $elements = $characters
                ? $newsFeed->freshPlayersThenOthers($max)
                : $newsFeed->latestAmong($group['keys'], $max, $group['criteria'] ?? []);

            if (!empty($elements)) {
                $groups[] = $group + [
                    'perRow' => ['desktop' => $portrait && !($group['large'] ?? false) ? $max / 2 : $max, 'tablet' => $max / 2],
                    'elements' => $elements,
                ];
            }
        }

        return $groups;
    }

    private function groupSections(NewsFeed $newsFeed, array $sections): array
    {
        foreach ($sections as $name => $section) {
            if (isset($section['share'])) {
                $shared = count($sections[$section['share']]['elements']);
                $sections[$section['share']]['max'] = $shared;
                $section['max'] -= $shared;
            }

            $section['elements'] = $this->sectionElements($newsFeed, $section);
            $sections[$name] = $section;
        }

        return array_values(array_filter($sections, fn ($section) => !empty($section['elements'])));
    }

    private function sectionElements(NewsFeed $newsFeed, array $section): array
    {
        if ($section['max'] <= 0) {
            return [];
        }

        $candidates = [];
        $taken = [];
        $total = 0;
        foreach ($section['pools'] ?? [$section] as $name => $pool) {
            $candidates[$name] = $newsFeed->latestAmong($pool['keys'], $section['max'], $pool['criteria'] ?? []);
            $taken[$name] = min($pool['quota'] ?? 0, count($candidates[$name]), $section['max'] - $total);
            $total += $taken[$name];
        }

        do {
            $added = false;
            foreach (array_keys($candidates) as $name) {
                if ($total < $section['max'] && $taken[$name] < count($candidates[$name])) {
                    $taken[$name]++;
                    $total++;
                    $added = true;
                }
            }
        } while ($added);

        $elements = [];
        foreach ($candidates as $name => $list) {
            $elements = array_merge($elements, array_slice($list, 0, $taken[$name]));
        }

        return $elements;
    }
}
