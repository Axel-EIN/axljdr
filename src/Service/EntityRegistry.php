<?php

namespace App\Service;

use App\Entity\Archive;
use App\Entity\Avantage;
use App\Entity\Clan;
use App\Entity\Competence;
use App\Entity\Ecole;
use App\Entity\Episode;
use App\Entity\Library;
use App\Entity\Lieu;
use App\Entity\Lore;
use App\Entity\Objet;
use App\Entity\Personnage;
use App\Entity\Rule;
use App\Entity\Sort;

final class EntityRegistry
{
    private const ENTITIES = [
        'episode'    => ['class' => Episode::class,    'route' => 'aventure_episode',  'label' => 'Épisode'],
        'personnage' => ['class' => Personnage::class, 'route' => 'personnage_profil', 'label' => 'Personnage'],
        'archive'    => ['class' => Archive::class,    'route' => 'empire_archive',    'label' => 'Archive'],
        'clan'       => ['class' => Clan::class,       'route' => 'empire_clan',       'label' => 'Faction'],
        'lieu'       => ['class' => Lieu::class,       'route' => 'empire_lieu',       'label' => 'Lieu'],
        'lore'       => ['class' => Lore::class,       'route' => 'empire_lore',       'label' => 'Lore'],
        'rule'       => ['class' => Rule::class,       'route' => 'regles_rule',       'label' => 'Règle'],
        'library'    => ['class' => Library::class,    'route' => 'regles_library',    'label' => 'Bibliothèque'],
        'ecole'      => ['class' => Ecole::class,      'route' => 'regles_ecole',      'label' => 'École'],
        'objet'      => ['class' => Objet::class,      'route' => null,                'label' => 'Objet'],
        'sort'       => ['class' => Sort::class,       'route' => null,                'label' => 'Sort'],
        'competence' => ['class' => Competence::class, 'route' => null,                'label' => 'Compétence'],
        'avantage'   => ['class' => Avantage::class,   'route' => null,                'label' => 'Avantage'],
    ];

    public static function keyOf($element): ?string
    {
        foreach (self::ENTITIES as $key => $entity) {
            if ($element instanceof $entity['class']) {
                return $key;
            }
        }

        return null;
    }

    public static function className(string $key): ?string
    {
        return self::ENTITIES[$key]['class'] ?? null;
    }

    public static function route(string $key): ?string
    {
        return self::ENTITIES[$key]['route'] ?? null;
    }

    public static function label(string $key): ?string
    {
        return self::ENTITIES[$key]['label'] ?? null;
    }

    public static function keys(): array
    {
        return array_keys(self::ENTITIES);
    }
}
