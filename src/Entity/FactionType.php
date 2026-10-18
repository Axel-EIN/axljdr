<?php

namespace App\Entity;

final class FactionType
{
    public const MAJEUR = 0;
    public const MINEUR = 1;
    public const NON_ALIGNE = 2;
    public const ENNEMI = 3;

    public const RANG_SANS_FACTION = 99;

    public const LABELS = [
        self::MAJEUR => 'Clan Majeur',
        self::MINEUR => 'Clan Mineur',
        self::NON_ALIGNE => 'Non aligné',
        self::ENNEMI => 'Ennemi',
    ];

    public const GROUPS = [
        self::MAJEUR => 'Clans Majeurs',
        self::MINEUR => 'Clans Mineurs',
        self::NON_ALIGNE => 'Non alignés et autres',
        self::ENNEMI => 'Ennemis',
    ];
}
