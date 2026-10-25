<?php

namespace App\Service;

use App\Entity\Competence;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;

class SheetReader
{
    public const TRAITS = ['constitution', 'volonte', 'reflexes', 'intuition', 'agilite', 'intelligence', 'forceStat', 'perception', 'vide'];

    public const GENDERS = ['M' => 'Homme', 'F' => 'Femme'];

    private const CATEGORY_ORDER = ['OBJET' => 0, 'ARME' => 1, 'PROJECTILE' => 2, 'ARMURE' => 3];

    public function traits(FichePersonnage $sheet): array
    {
        $traits = [];

        foreach (self::TRAITS as $name) {
            $traits[$name] = ['value' => $sheet->attributEffectif($name), 'bonus' => $sheet->bonusAttribut($name)];
        }

        return $traits;
    }

    public function rings(array $traits): array
    {
        return [
            'terre' => min($traits['constitution']['value'], $traits['volonte']['value']),
            'air' => min($traits['reflexes']['value'], $traits['intuition']['value']),
            'eau' => min($traits['forceStat']['value'], $traits['perception']['value']),
            'feu' => min($traits['agilite']['value'], $traits['intelligence']['value']),
            'vide' => $traits['vide']['value'],
        ];
    }

    public function initiative(FichePersonnage $sheet, int $rank): string
    {
        $reflexes = $sheet->attributEffectif('reflexes');
        $initiative = ($reflexes + $rank) . 'g' . $reflexes;
        $modifier = (int) $sheet->getInitiativeModifier();

        if ($modifier !== 0) {
            $initiative .= ($modifier > 0 ? '+' : '') . $modifier;
        }

        return $initiative;
    }

    public function armorTn(FichePersonnage $sheet, int $rank): int
    {
        return $sheet->attributEffectif('reflexes') * 5 + $rank;
    }

    public function reduction(FichePersonnage $sheet): int
    {
        $armor = $sheet->getArmure();

        return ($armor ? (int) $armor->getReduction() : 0) + (int) $sheet->getReductionModifier();
    }

    public function recovery(FichePersonnage $sheet, int $rank): int
    {
        return $sheet->attributEffectif('constitution') * 2 + $rank;
    }

    public function skills(FichePersonnage $sheet): array
    {
        $skills = [];

        for ($i = 1; $i <= 20; $i++) {
            $skill = $sheet->{'getCompetence' . $i}();

            if ($skill === null) {
                continue;
            }

            $skills[] = [
                'skill' => $skill,
                'value' => (int) $sheet->{'getValeur' . $i}(),
                'dojo' => (int) $sheet->{'getCompEcole' . $i}(),
                'specialisations' => $this->specialisations($skill, (string) $sheet->{'getSpecialisations' . $i}()),
                'tainted' => (bool) $skill->getDegradante(),
            ];
        }

        usort($skills, function (array $a, array $b) {
            return [$a['dojo'] > 0 ? 0 : 1, $this->sortable($a['skill']->getNom())]
                <=> [$b['dojo'] > 0 ? 0 : 1, $this->sortable($b['skill']->getNom())];
        });

        return $skills;
    }

    public function items(FichePersonnage $sheet): array
    {
        $items = $sheet->getInventoryItems()->toArray();

        usort($items, function ($a, $b) {
            return [self::CATEGORY_ORDER[$a->getCategorie()] ?? count(self::CATEGORY_ORDER), $this->sortable($a->getNom())]
                <=> [self::CATEGORY_ORDER[$b->getCategorie()] ?? count(self::CATEGORY_ORDER), $this->sortable($b->getNom())];
        });

        return $items;
    }

    public function slug(Personnage $character): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $character->getNomComplet());
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $ascii === false ? 'personnage' : $ascii) ?? '');

        return trim($slug, '-');
    }

    public function sortable(string $name): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        return strtolower($ascii === false ? $name : $ascii);
    }

    private function specialisations(Competence $skill, string $bitmask): array
    {
        $labels = [];

        for ($k = 1; $k <= 6; $k++) {
            $label = $skill->{'getSpecialisation' . $k}();

            if ($label && ($bitmask[$k - 1] ?? '0') === '1') {
                $labels[] = $label;
            }
        }

        return $labels;
    }
}
