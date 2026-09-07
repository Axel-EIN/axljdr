<?php

namespace App\Entity;

final class Status
{
    public const ALIVE = 0;
    public const MISSING = 1;
    public const DEAD = 2;

    public const LABELS = [
        self::ALIVE => 'Vivant',
        self::MISSING => 'Disparu',
        self::DEAD => 'Mort',
    ];
}
