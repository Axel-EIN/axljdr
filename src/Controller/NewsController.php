<?php

namespace App\Controller;

use App\Repository\LieuRepository;
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

    private const PARTICIPATIONS = 3;

    private const SEANCES_PRECEDENTES = 3;

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
    public function viewNews(NewsFeed $newsFeed, ClasseurXP $classeurXP, CurrentPlayer $currentPlayer, Visibility $visibility, Localisateur $localisateur, LieuRepository $lieuRepository): Response
    {
        $mesPersonnages = $currentPlayer->characterIds();
        $monPersonnage = $currentPlayer->main();
        $maPositionLibre = $localisateur->positionLibre($monPersonnage);
        $maPosition = $maPositionLibre === null ? $localisateur->position($monPersonnage) : null;

        $participations = $newsFeed->lastParticipations(self::PARTICIPATIONS);
        $carteVisible = $participations !== [] && ($maPosition !== null || $maPositionLibre !== null);
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
            ['entity' => 'aventure', 'titleLight' => 'Dernière ', 'titleStrong' => 'Séance'],
            ['entity' => 'nouveautes', 'titleLight' => '', 'titleStrong' => 'Nouveautés'],
        ];

        $groupes = [];
        $secretsParEntite = $newsFeed->unlockedByEntity(self::VIGNETTES_PORTRAITS);

        $trouver = function (array $cles, int $max, array $criteres = []) use ($newsFeed, $visibility, $secretsParEntite) {
            $elements = [];
            foreach ($cles as $cle) {
                $elements = array_merge($elements, $secretsParEntite[$cle] ?? []);
            }
            $elements = array_filter($elements, function ($element) use ($criteres) {
                return $this->respecte($element, $criteres);
            });
            $elements = array_merge($elements, $newsFeed->latestAmong($cles, $max, $criteres));

            return array_slice($visibility->listedOnly($elements), 0, $max);
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

        return $this->render('news/index.html.twig', [
            'dernier_episode' => $dernierEpisode,
            'classement' => $dernierEpisode === null ? false : $classeurXP->classerPersosEpisode($dernierEpisode),
            'session_locations' => $dernierEpisode === null ? [] : $newsFeed->episodeLocations($dernierEpisode),
            'session_characters' => $dernierEpisode === null ? [] : $newsFeed->episodeCharacters($dernierEpisode),
            'session_objects' => $dernierEpisode === null ? [] : $newsFeed->episodeObjects($dernierEpisode),
            'session_previous' => array_slice($episodes, 1),
            'mes_personnages' => $mesPersonnages,
            'mon_personnage' => $monPersonnage,
            'mes_participations' => $participations,
            'classement_general' => $newsFeed->generalRanking(),
            'participations_xp' => $participationsXp,
            'ma_position' => $maPosition,
            'ma_position_libre' => $maPositionLibre,
            'carte_visible' => $carteVisible,
            'lieux' => $carteVisible ? $lieuRepository->findAllSorted() : [],
            'groupes' => $groupes,
            'secrets_large' => $colonne === 0,
            'sections' => $sections,
            'header_classname' => 'news',
            'header_up' => 'Accueil',
            'header_down' => 'Bienvenue à Rokugan',
            'category' => 'news',
        ]);
    }

    private function respecte($element, array $criteres): bool
    {
        foreach ($criteres as $champ => $valeur) {
            $actuelle = $element->{'get' . ucfirst($champ)}();

            if (is_array($valeur) ? !in_array($actuelle, $valeur, true) : $actuelle !== $valeur) {
                return false;
            }
        }

        return true;
    }
}
