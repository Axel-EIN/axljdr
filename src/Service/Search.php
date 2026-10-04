<?php

namespace App\Service;

use App\Entity\Archive;
use App\Entity\Avantage;
use App\Entity\Clan;
use App\Entity\Competence;
use App\Entity\Ecole;
use App\Entity\Episode;
use App\Entity\Famille;
use App\Entity\Lieu;
use App\Entity\Lore;
use App\Entity\Objet;
use App\Entity\Personnage;
use App\Entity\Rule;
use App\Entity\Scene;
use App\Entity\Sort;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\String\u;

class Search
{
    public const MIN_LENGTH = 2;

    private const EXCERPT_BEFORE = 60;
    private const EXCERPT_LENGTH = 160;

    private const PARTS = ['part1', 'part2', 'part3', 'part4', 'part5'];

    private const SOURCES = [
        Avantage::class   => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description'], 'library' => true],
        Competence::class => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description'], 'library' => true],
        Objet::class      => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description'], 'library' => true],
        Sort::class       => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description'], 'library' => true],
        Ecole::class      => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description']],
        Clan::class       => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description', 'longDescription']],
        Famille::class    => ['label' => 'nom', 'names' => ['nom'], 'texts' => ['description'], 'parent' => 'clan', 'joins' => ['clan']],
        Personnage::class => ['label' => 'nomComplet', 'names' => ['prenom', 'nom', 'titres'], 'texts' => ['description'], 'joins' => ['fichePersonnage']],
        Rule::class       => ['label' => 'nom', 'names' => ['nom'], 'texts' => self::PARTS],
        Lore::class       => ['label' => 'nom', 'names' => ['nom'], 'texts' => self::PARTS],
        Archive::class    => ['label' => 'titre', 'names' => ['titre'], 'texts' => ['contenu']],
        Lieu::class       => ['label' => 'nom', 'names' => ['nom', 'surnom'], 'texts' => ['description']],
        Episode::class    => ['label' => 'titre', 'names' => ['titre'], 'texts' => ['resume']],
        Scene::class      => ['label' => 'titre', 'names' => ['titre'], 'texts' => ['texte'], 'parent' => 'episodeParent', 'joins' => ['episodeParent']],
    ];

    private const GROUPS = [
        'avantage'      => ['label' => 'Avantage',      'class' => Avantage::class,   'where' => "e.genre = 'Avantage'"],
        'desavantage'   => ['label' => 'Désavantage',   'class' => Avantage::class,   'where' => "e.genre = 'Désavantage'"],
        'competence'    => ['label' => 'Compétence',    'class' => Competence::class],
        'arme'          => ['label' => 'Arme',          'class' => Objet::class,      'where' => "e.categorie IN ('ARME', 'PROJECTILE')"],
        'sort'          => ['label' => 'Sort',          'class' => Sort::class,       'where' => "e.categorie = 'MAGIE'"],
        'tatouage'      => ['label' => 'Tatouage',      'class' => Sort::class,       'where' => "e.categorie = 'TATOUAGE'"],
        'kiho'          => ['label' => 'Kiho',          'class' => Sort::class,       'where' => "e.categorie = 'KIHO'"],
        'maho'          => ['label' => 'Maho',          'class' => Sort::class,       'where' => "e.categorie = 'MAHO'"],
        'armure'        => ['label' => 'Armure',        'class' => Objet::class,      'where' => "e.categorie = 'ARMURE'"],
        'ecole'         => ['label' => 'École',         'class' => Ecole::class],
        'clan'          => ['label' => 'Faction',       'class' => Clan::class],
        'famille'       => ['label' => 'Famille',       'class' => Famille::class],
        'personnage'    => ['label' => 'Personnage',    'class' => Personnage::class],
        'regle-base'    => ['label' => 'Règle de base', 'class' => Rule::class,       'where' => 'e.base = true'],
        'regle-annexe'  => ['label' => 'Règle annexe',  'class' => Rule::class,       'where' => 'e.base = false'],
        'lore'          => ['label' => 'Lore',          'class' => Lore::class],
        'archive'       => ['label' => 'Archive',       'class' => Archive::class],
        'lieu'          => ['label' => 'Lieu',          'class' => Lieu::class],
        'objet'         => ['label' => 'Objet',         'class' => Objet::class,      'where' => "e.categorie = 'OBJET'"],
        'episode'       => ['label' => 'Épisode',       'class' => Episode::class],
        'scene'         => ['label' => 'Scène',         'class' => Scene::class],
    ];

    private const VARIANTS = [
        'a' => 'aàâäáā',
        'c' => 'cç',
        'e' => 'eéèêëē',
        'i' => 'iîïíī',
        'o' => 'oôöóō',
        'u' => 'uùûüúū',
        'y' => 'yÿ',
        "'" => "'’",
    ];

    private $entityManager;
    private $visibility;
    private $elementUrl;

    public function __construct(EntityManagerInterface $entityManager, Visibility $visibility, ElementUrl $elementUrl)
    {
        $this->entityManager = $entityManager;
        $this->visibility = $visibility;
        $this->elementUrl = $elementUrl;
    }

    public function find(string $term): array
    {
        $groups = [];

        foreach ($this->results($term) as $result) {
            $groups[$result['group']]['label'] = $result['groupLabel'];
            $groups[$result['group']]['results'][] = $result;
        }

        return $groups;
    }

    public function suggest(string $term, int $limit): array
    {
        $results = $this->results($term);
        $order = array_flip(array_keys(self::GROUPS));

        usort($results, function ($a, $b) use ($order) {
            return [!$a['inName'], $order[$a['group']]] <=> [!$b['inName'], $order[$b['group']]];
        });

        return array_slice($results, 0, $limit);
    }

    private function results(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < self::MIN_LENGTH) {
            return [];
        }

        $pattern = $this->pattern($term);
        $results = [];

        foreach (self::GROUPS as $group => $config) {
            $groupResults = [];

            foreach ($this->candidates($term, $config) as $element) {
                $result = $this->match($element, $group, $pattern);

                if ($result !== null) {
                    $groupResults[] = $result;
                }
            }

            usort($groupResults, function ($a, $b) {
                return [!$a['inName'], $this->normalize($a['label'])] <=> [!$b['inName'], $this->normalize($b['label'])];
            });

            $results = array_merge($results, $groupResults);
        }

        return $results;
    }

    private function candidates(string $term, array $config): array
    {
        $source = self::SOURCES[$config['class']];
        $fields = array_merge($source['names'], $source['texts']);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($config['class'], 'e')
            ->setParameter('term', '%' . addcslashes($term, '%_\\') . '%');

        foreach ($source['joins'] ?? [] as $join) {
            $qb->leftJoin('e.' . $join, $join)->addSelect($join);
        }

        $qb->andWhere($qb->expr()->orX(...array_map(function ($field) {
            return 'e.' . $field . ' LIKE :term';
        }, $fields)));

        if (isset($config['where'])) {
            $qb->andWhere($config['where']);
        }

        return $qb->getQuery()->getResult();
    }

    private function match($element, string $group, string $pattern): ?array
    {
        $source = self::SOURCES[self::GROUPS[$group]['class']];

        $inName = false;
        foreach ($source['names'] as $field) {
            if (preg_match($pattern, (string) $this->read($element, $field))) {
                $inName = true;
                break;
            }
        }

        $excerpt = null;
        if (!$inName) {
            foreach ($source['texts'] as $field) {
                $excerpt = $this->excerpt($this->plain($this->read($element, $field)), $pattern);

                if ($excerpt !== null) {
                    break;
                }
            }

            if ($excerpt === null) {
                return null;
            }
        }

        $target = isset($source['parent']) ? $this->read($element, $source['parent']) : $element;

        if ($target === null) {
            return null;
        }

        $state = $this->visibility->state($target);
        $lockedInLibrary = isset($source['library']) && $inName && $state === Visibility::LOCKED;

        if (!$lockedInLibrary && !$this->visibility->isReadable($target)) {
            return null;
        }

        $url = $lockedInLibrary ? $this->elementUrl->inLibrary($element) : $this->elementUrl->of($element);

        if ($url === null) {
            return null;
        }

        return [
            'element' => $element,
            'label' => (string) $this->read($element, $source['label']),
            'group' => $group,
            'groupLabel' => self::GROUPS[$group]['label'],
            'url' => $url,
            'state' => $state,
            'inName' => $inName,
            'excerpt' => $excerpt,
        ];
    }

    private function excerpt(string $text, string $pattern): ?array
    {
        if (!preg_match($pattern, $text, $found, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $match = $found[0][0];
        $before = substr($text, 0, $found[0][1]);
        $after = substr($text, $found[0][1] + strlen($match));
        $afterLength = max(0, self::EXCERPT_LENGTH - self::EXCERPT_BEFORE - mb_strlen($match));
        $cutStart = mb_strlen($before) > self::EXCERPT_BEFORE;
        $cutEnd = mb_strlen($after) > $afterLength;

        return [
            'cutStart' => $cutStart,
            'before' => $cutStart ? preg_replace('/^\S*\s+/u', '', mb_substr($before, -self::EXCERPT_BEFORE)) : $before,
            'match' => $match,
            'after' => $cutEnd ? preg_replace('/\s+\S*$/u', '', mb_substr($after, 0, $afterLength)) : $after,
            'cutEnd' => $cutEnd,
        ];
    }

    private function pattern(string $term): string
    {
        $pattern = '';

        foreach (mb_str_split($term) as $char) {
            $base = $this->normalize($char);
            $pattern .= isset(self::VARIANTS[$base]) ? '[' . self::VARIANTS[$base] . ']' : preg_quote($char, '/');
        }

        return '/(?<![\p{L}\p{N}])' . $pattern . '/iu';
    }

    private function plain(?string $html): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function normalize(string $text): string
    {
        return u($text)->ascii()->lower()->toString();
    }

    private function read($element, string $field)
    {
        return $element->{'get' . ucfirst($field)}();
    }
}
