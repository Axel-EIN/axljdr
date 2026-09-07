<?php

namespace App\Controller;

use App\Service\ClasseurXP;
use App\Service\CurrentPlayer;
use App\Service\NewsFeed;
use App\Service\Visibility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private const PAR_LIGNE_PERSONNAGES = 6;
    private const PAR_LIGNE_PERSONNAGES_SECRET = 4;

    private const PAR_LIGNE_LIEUX = 4;
    private const PAR_LIGNE_LIEUX_SECRET = 3;

    private const PAR_LIGNE_OBJETS = 4;
    private const PAR_LIGNE_OBJETS_SECRET = 3;

    private const PAR_LIGNE_ARCHIVES = 4;
    private const PAR_LIGNE_ARCHIVES_SECRET = 3;

    private const PAR_LIGNE_LORE = 4;
    private const PAR_LIGNE_LORE_SECRET = 3;

    private const PAR_LIGNE_FACTIONS = 4;
    private const PAR_LIGNE_FACTIONS_SECRET = 3;

    private const PAR_LIGNE_REGLES = 4;
    private const PAR_LIGNE_REGLES_SECRET = 3;

    private const PAR_LIGNE_BIBLIOTHEQUES = 4;
    private const PAR_LIGNE_BIBLIOTHEQUES_SECRET = 3;

    private const PAR_LIGNE_ECOLES = 4;
    private const PAR_LIGNE_ECOLES_SECRET = 3;

    private const PAR_LIGNE_SORTS = 5;
    private const PAR_LIGNE_SORTS_SECRET = 4;

    private const PAR_LIGNE_COMPETENCES = 5;
    private const PAR_LIGNE_COMPETENCES_SECRET = 4;

    private const PAR_LIGNE_AVANTAGES = 5;
    private const PAR_LIGNE_AVANTAGES_SECRET = 4;

    private const SECRETS_PAR_ENTITE = 12;

    private const PARTICIPATIONS = 5;

    private const SEANCES_PRECEDENTES = 5;

    private const RUBRIQUES = [
        [
            'entity' => 'decouvertes', 'titleLight' => 'Dernières ', 'titleStrong' => 'Découvertes',
            'groupes' => [
                ['titre' => 'Personnages', 'secretTitle' => 'Rencontres Importantes ou Secrètes', 'variant' => 'portrait',
                    'perRow' => self::PAR_LIGNE_PERSONNAGES, 'secretPerRow' => self::PAR_LIGNE_PERSONNAGES_SECRET, 'cles' => ['personnage']],
                ['titre' => 'Lieux', 'secretTitle' => 'Visites Importantes ou Secrètes', 'variant' => 'icon',
                    'perRow' => self::PAR_LIGNE_LIEUX, 'secretPerRow' => self::PAR_LIGNE_LIEUX_SECRET, 'cles' => ['lieu']],
                ['titre' => 'Objets', 'variant' => 'rule', 'secretTitle' => 'Objets de Valeur ou Secret', 'perRow' => self::PAR_LIGNE_OBJETS, 'secretPerRow' => self::PAR_LIGNE_OBJETS_SECRET, 'cles' => ['objet']],
            ],
        ],
        [
            'entity' => 'informations', 'titleLight' => 'Dernières ', 'titleStrong' => 'Informations',
            'groupes' => [
                ['titre' => 'Archives', 'secretTitle' => 'Archives secrètes', 'perRow' => self::PAR_LIGNE_ARCHIVES, 'secretPerRow' => self::PAR_LIGNE_ARCHIVES_SECRET, 'cles' => ['archive']],
                ['titre' => 'Lore', 'secretTitle' => 'Lore secrets', 'perRow' => self::PAR_LIGNE_LORE, 'secretPerRow' => self::PAR_LIGNE_LORE_SECRET, 'cles' => ['lore']],
                ['titre' => 'Factions', 'secretTitle' => 'Factions secrètes', 'variant' => 'emblem', 'perRow' => self::PAR_LIGNE_FACTIONS, 'secretPerRow' => self::PAR_LIGNE_FACTIONS_SECRET, 'cles' => ['clan']],
            ],
        ],
        [
            'entity' => 'mecaniques', 'titleLight' => 'Dernières ', 'titleStrong' => 'Mécaniques', 'hideEmpty' => true,
            'groupes' => [
                ['titre' => 'Règles', 'variant' => 'rule', 'secretTitle' => 'Règles secrètes', 'perRow' => self::PAR_LIGNE_REGLES, 'secretPerRow' => self::PAR_LIGNE_REGLES_SECRET, 'cles' => ['rule']],
                ['titre' => 'Bibliothèques', 'variant' => 'rule', 'secretTitle' => 'Bibliothèques secrètes', 'perRow' => self::PAR_LIGNE_BIBLIOTHEQUES, 'secretPerRow' => self::PAR_LIGNE_BIBLIOTHEQUES_SECRET, 'cles' => ['library']],
                ['titre' => 'Écoles', 'variant' => 'rule', 'secretTitle' => 'Écoles secrètes', 'perRow' => self::PAR_LIGNE_ECOLES, 'secretPerRow' => self::PAR_LIGNE_ECOLES_SECRET, 'cles' => ['ecole']],
                ['titre' => 'Sorts', 'variant' => 'rule', 'secretTitle' => 'Sorts secrets', 'perRow' => self::PAR_LIGNE_SORTS, 'secretPerRow' => self::PAR_LIGNE_SORTS_SECRET, 'cles' => ['sort']],
                ['titre' => 'Compétences', 'variant' => 'rule', 'secretTitle' => 'Compétences secrètes', 'perRow' => self::PAR_LIGNE_COMPETENCES, 'secretPerRow' => self::PAR_LIGNE_COMPETENCES_SECRET, 'cles' => ['competence']],
                ['titre' => 'Avantages', 'variant' => 'rule', 'secretTitle' => 'Avantages secrets', 'perRow' => self::PAR_LIGNE_AVANTAGES, 'secretPerRow' => self::PAR_LIGNE_AVANTAGES_SECRET, 'cles' => ['avantage']],
            ],
        ],
    ];

    /**
     * @Route("/", name="news")
     */
    public function viewNews(NewsFeed $newsFeed, ClasseurXP $classeurXP, CurrentPlayer $currentPlayer, Visibility $visibility): Response
    {
        $mesPersonnages = $currentPlayer->characterIds();
        $secretsVisibles = !empty($mesPersonnages);

        $participations = $newsFeed->lastParticipations(self::PARTICIPATIONS);
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
        ];

        $rubriques = [];
        $secretsParEntite = $newsFeed->unlockedByEntity(self::SECRETS_PAR_ENTITE);

        foreach (self::RUBRIQUES as $rubrique) {
            $groupes = [];

            foreach ($rubrique['groupes'] as $groupe) {
                $secrets = [];
                foreach ($groupe['cles'] as $cle) {
                    $secrets = array_merge($secrets, $secretsParEntite[$cle] ?? []);
                }

                $groupe['perRow'] = $secretsVisibles
                    ? $groupe['perRow']
                    : $groupe['perRow'] + $groupe['secretPerRow'];

                $publie = $visibility->listedOnly($newsFeed->latestAmong($groupe['cles'], $groupe['perRow']));
                $secrets = $visibility->listedOnly(array_slice($secrets, 0, $groupe['secretPerRow']));

                if (empty($publie) && empty($secrets) && ($rubrique['hideEmpty'] ?? false)) {
                    continue;
                }

                $groupes[] = $groupe + [
                    'ratio' => '1610',
                    'publie' => $publie,
                    'secrets' => $secrets,
                ];
            }

            $rubriques[] = ['groupes' => $groupes];
        }

        foreach (self::RUBRIQUES as $rubrique) {
            $sections[] = [
                'entity' => $rubrique['entity'],
                'titleLight' => $rubrique['titleLight'],
                'titleStrong' => $rubrique['titleStrong'],
            ];
        }

        return $this->render('news/index.html.twig', [
            'dernier_episode' => $dernierEpisode,
            'classement' => $dernierEpisode === null ? false : $classeurXP->classerPersosEpisode($dernierEpisode),
            'session_locations' => $dernierEpisode === null ? [] : $newsFeed->episodeLocations($dernierEpisode),
            'session_characters' => $dernierEpisode === null ? [] : $newsFeed->episodeCharacters($dernierEpisode),
            'session_objects' => $dernierEpisode === null ? [] : $newsFeed->episodeObjects($dernierEpisode),
            'session_previous' => array_slice($episodes, 1),
            'mes_personnages' => $mesPersonnages,
            'mon_personnage' => $currentPlayer->characters()[0] ?? null,
            'mes_participations' => $participations,
            'classement_general' => $newsFeed->generalRanking(),
            'participations_xp' => $participationsXp,
            'rencontres' => $newsFeed->encounters($participations),
            'rubriques' => $rubriques,
            'sections' => $sections,
            'header_classname' => 'news',
            'header_up' => 'Accueil',
            'header_down' => 'Bienvenue a Rokugan',
            'category' => 'news',
        ]);
    }
}
