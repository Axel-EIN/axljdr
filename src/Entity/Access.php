<?php

namespace App\Entity;

final class Access
{
    public const SECRET = 0;
    public const LOCKED = 1;
    public const AUTO = 2;
    public const PUBLIC = 3;

    public const LABELS = [
        self::SECRET => 'Secret',
        self::LOCKED => 'Bloqué',
        self::AUTO => 'Automatique',
        self::PUBLIC => 'Public',
    ];

    public const CHOICES = [
        self::SECRET => 'Secret — invisible, révélé individuellement par le MJ',
        self::LOCKED => 'Bloqué — teasé sous cadenas, ouvert individuellement par le MJ',
        self::AUTO => 'Automatique — invisible, révélé par une rencontre ou une visite',
        self::PUBLIC => 'Public — lisible par tout le monde ; la date de publication, si elle est remplie, programme sa mise en ligne',
    ];

    public static function needsUnlock(int $access): bool
    {
        return $access <= self::AUTO;
    }

    public static function isHidden(int $access): bool
    {
        return $access === self::SECRET || $access === self::AUTO;
    }
}
