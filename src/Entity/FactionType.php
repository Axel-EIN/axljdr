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

    public const INTROS = [
        self::MAJEUR => "Les Clans Majeurs sont les plus grandes factions de Rokugan. Chacune d'elles est héritière d'un Kami originel qui jura fidélité à l'Empereur et se répartit la majeure partie de l'Empire. Elles se composent de plusieurs familles et forment des milliers de samuraïs dans leurs dojos.",
        self::MINEUR => "Les Clans Mineurs sont de petites factions bâties autour d'une seule famille. Chacun doit son nom et son mon à un acte de bravoure ou de loyauté que l'Empereur a jugé digne d'être récompensé. Moins puissants que les Clans Majeurs, ils survivent grâce à leurs alliances, leur ruse et leur spécialisation.",
        self::NON_ALIGNE => 'Certaines factions ne prêtent allégeance à aucun clan. Moines de la Confrérie de Shinsei, Nezumi ou ronin suivent leur propre voie.',
        self::ENNEMI => "Au-delà de la Muraille Kaiu et dans l'ombre de l'Empire, des puissances menacent Rokugan. Créatures de l'Outre-Monde, Adeptes du Sang ou servants de l'Ombre Rampante cherchent à corrompre ou à détruire l'Empire d'Émeraude.",
    ];

    public static function siblingTypes(int $type): array
    {
        $autres = [self::NON_ALIGNE, self::ENNEMI];

        return in_array($type, $autres, true) ? $autres : [$type];
    }
}
