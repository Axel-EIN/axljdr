<?php

namespace App\Service;

use App\Entity\Competence;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;

class SheetExport
{
    private const CANVAS_WIDTH = 1654;
    private const SHEETS = '/assets/sheets/';
    private const DICE_ICON = '/assets/ui/dice.png';
    private const FRONT = 'Character Sheet Recto Base.png';
    private const BACK = 'Character Sheet Verso Base.png';
    private const FRAME = 'Cadre Illu.png';
    private const RINGS_OVERLAY = 'Character Sheet Verso Elements.png';
    private const TECHNIQUES_OVERLAY = 'Character Sheet Verso Techniques.png';

    private const ILLUSTRATION = [1136, 45, 472, 548];
    private const ILLUSTRATION_FOCUS = 0.1;
    private const FRAME_BOX = [1123, 28, 503, 585];

    private const IDENTITY = [
        'name' => [166, 61, 360],
        'clan' => [166, 110, 360],
        'dojo' => [166, 161, 360],
        'rank' => [801, 57, 280],
        'exp' => [801, 107, 85],
        'remaining' => [987, 107, 95],
        'player' => [801, 157, 280],
    ];

    private const TRAITS = [
        'constitution' => [394, 312, 241, 322],
        'volonte' => [371, 419, 250, 440],
        'forceStat' => [318, 526, 210, 553],
        'perception' => [337, 638, 194, 678],
        'reflexes' => [757, 318, 884, 322],
        'intuition' => [773, 418, 913, 440],
        'agilite' => [822, 526, 936, 553],
        'intelligence' => [808, 638, 960, 678],
    ];

    private const RINGS = [
        'terre' => [517, 435],
        'air' => [632, 436],
        'eau' => [485, 537],
        'feu' => [663, 537],
        'vide' => [572, 600],
    ];
    private const VOID_BONUS = [624, 784];

    private const STANDING_RIGHT = 1116;
    private const STANDINGS = ['honneur' => 684, 'gloire' => 716, 'infamie' => 750, 'statut' => 782];

    private const COMBAT_LABEL = [1152, 1178];
    private const COMBAT_LABEL_SIZE = 19;
    private const COMBAT_VALUE = [1447, 94];
    private const COMBAT_VALUE_SIZE = 28;
    private const COMBAT_DICE = [1152, 870, 20];
    private const COMBAT_INITIATIVE = [887, 908, 902];
    private const COMBAT_ND = [982, 986];
    private const COMBAT_ARMOUR = [1317, 1063];
    private const COMBAT_REDUCTION = [1556, 1063];
    private const COMBAT_RECOVERY = [1556, 1666];
    private const HP_COLUMN = 1437;
    private const HP_FIRST = 1177;
    private const HP_STEP = 49;
    private const HP_ROWS = 8;

    private const SPECIALISATION_SIZE = 19;
    private const MASTERY_SIZE = 17;
    private const MASTERY_LEADING = 20;
    private const MASTERY_TOP = -28;
    private const MASTERY_LINES = 2;

    private const SKILL_ROWS = 25;
    private const SKILL_FIRST = 990;
    private const SKILL_STEP = 50;
    private const SKILL_COLUMNS = [
        'dot' => 55,
        'name' => [82, 258],
        'rank' => 392,
        'spec' => [445, 181],
        'mastery3' => [648, 218],
        'mastery6' => [888, 217],
    ];

    private const WEAPON_FIRST = 1785;
    private const WEAPON_STEP = 37;
    private const WEAPON_ROWS = 4;
    private const WEAPON_CATEGORY = 'ARME';
    private const CATEGORY_ORDER = ['OBJET' => 0, 'ARME' => 1, 'PROJECTILE' => 2, 'ARMURE' => 3];
    private const WEAPON_COLUMNS = [
        [1148, 100, PdfWriter::ALIGN_LEFT],
        [1317, 110, PdfWriter::ALIGN_CENTER],
        [1437, 110, PdfWriter::ALIGN_CENTER],
        [1556, 105, PdfWriter::ALIGN_CENTER],
    ];

    private const RECALL_ADVANTAGE = 2003;
    private const RECALL_DISADVANTAGE = 2167;
    private const RECALL_STEP = 41;
    private const RECALL_ROWS = 3;

    private const OVERLAY_BOX = [573, 40];
    private const OVERLAY_TEXT = [593, 500];
    private const OVERLAY_SIZE = 21;
    private const RING_BADGE_RIGHT = 1065;
    private const RING_BADGES = ['TERRE' => 323, 'AIR' => 562, 'FEU' => 801, 'EAU' => 1043];

    private const TATTOOS_PER_RANK = 2;

    private const OVERLAY_BLOCKS = [
        [129, 172, 215, 259],
        [370, 413, 456, 500],
        [610, 653, 696, 740],
        [850, 893, 936, 980],
        [1094, 1137, 1180, 1224, 1268],
    ];

    private const SEX_LABELS = ['M' => 'Homme', 'F' => 'Femme'];
    private const IDENTITY_BOXES = [
        'sex' => [234, 115],
        'age' => [491, 115],
        'height' => [234, 164],
        'weight' => [491, 164],
        'hair' => [234, 213],
        'eyes' => [491, 213],
    ];

    private const APPEARANCE_BOX = [52, 298, 490];
    private const APPEARANCE_LEADING = 41;
    private const APPEARANCE_LINES = 4;

    private const NOTES_BOX = [628, 1586, 980];
    private const NOTES_LEADING = 50;
    private const NOTES_LINES = 14;

    private const INVENTORY_FIRST = 151;
    private const INVENTORY_STEP = 41;
    private const INVENTORY_ROWS = 22;
    private const INVENTORY_TEXT = [1152, 365];

    private const PURSE_BASELINE = 1240;
    private const PURSE_COLUMNS = ['koku' => 1219, 'bu' => 1378, 'zeni' => 1536];

    private const DETAIL_TEXT = [52, 430];
    private const DETAIL_POINTS = 548;
    private const DETAIL_STEP = 41;
    private const DETAIL_ADVANTAGE = [514, 8];
    private const DETAIL_DISADVANTAGE = [883, 9];

    private string $publicDirectory;
    private XpRanker $xpRanker;

    public function __construct(string $publicDirectory, XpRanker $xpRanker)
    {
        $this->publicDirectory = $publicDirectory;
        $this->xpRanker = $xpRanker;
    }

    public function build(FichePersonnage $sheet): string
    {
        $character = $sheet->getPersonnage();
        $traits = $this->traits($sheet);
        $rank = $this->xpRanker->rank($this->xpRanker->total($character));
        $pdf = new PdfWriter(self::CANVAS_WIDTH);

        $pdf->background($this->sheet(self::FRONT));
        $this->identity($pdf, $sheet, $rank);
        $this->illustration($pdf, $character);
        $this->traitCircles($pdf, $traits);
        $this->standings($pdf, $sheet);
        $this->combat($pdf, $sheet, $traits, $rank);
        $this->skills($pdf, $sheet);
        $this->weapons($pdf, $sheet);
        $this->recalls($pdf, $sheet);

        $pdf->newPage();
        $pdf->background($this->sheet(self::BACK));
        $this->personalInfo($pdf, $sheet);
        $this->inventory($pdf, $sheet);
        $this->purse($pdf, $sheet);
        $this->details($pdf, $sheet);
        $this->techniques($pdf, $sheet, $rank);

        return $pdf->render();
    }

    public function filename(FichePersonnage $sheet): string
    {
        $character = $sheet->getPersonnage();
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $character->getNomComplet());
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $ascii === false ? 'personnage' : $ascii) ?? '');

        return 'fiche-' . trim($slug, '-') . '.pdf';
    }

    private function sheet(string $name): string
    {
        return $this->publicDirectory . self::SHEETS . $name;
    }

    private function traits(FichePersonnage $sheet): array
    {
        $traits = [];

        foreach (['constitution', 'volonte', 'reflexes', 'intuition', 'agilite', 'intelligence', 'forceStat', 'perception', 'vide'] as $name) {
            $traits[$name] = ['value' => $sheet->attributEffectif($name), 'bonus' => $sheet->bonusAttribut($name)];
        }

        return $traits;
    }

    private function identity(PdfWriter $pdf, FichePersonnage $sheet, int $rank): void
    {
        $character = $sheet->getPersonnage();

        $values = [
            'name' => $character->getNomComplet(),
            'clan' => $character->getClan() ? $character->getClan()->getNom() : '',
            'dojo' => $character->getEcole() ? $character->getEcole()->getNom() : '',
            'rank' => (string) $rank,
            'exp' => (string) $this->xpRanker->total($sheet->getPersonnage()),
            'remaining' => (string) $this->xpRanker->remaining($sheet),
            'player' => $character->getJoueur() ? $character->getJoueur()->getPseudo() : '',
        ];

        foreach (self::IDENTITY as $key => [$x, $y, $width]) {
            $pdf->text($values[$key], $x, $y, 28, ['bold' => true, 'width' => $width]);
        }
    }

    private function illustration(PdfWriter $pdf, Personnage $character): void
    {
        if ($character->getIllustration()) {
            [$x, $y, $width, $height] = self::ILLUSTRATION;
            $pdf->cover($this->publicDirectory . '/' . ltrim($character->getIllustration(), '/'), $x, $y, $width, $height, self::ILLUSTRATION_FOCUS);
        }

        [$x, $y, $width, $height] = self::FRAME_BOX;
        $pdf->overlay($this->sheet(self::FRAME), $x, $y, $width, $height);
    }

    private function traitCircles(PdfWriter $pdf, array $traits): void
    {
        foreach (self::TRAITS as $name => [$x, $y, $labelX, $labelY]) {
            $pdf->text((string) $traits[$name]['value'], $x, $y + 12, 34, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);

            $this->traitBonus($pdf, $traits[$name]['bonus'], $labelX, $labelY);
        }

        $rings = [
            'terre' => min($traits['constitution']['value'], $traits['volonte']['value']),
            'air' => min($traits['reflexes']['value'], $traits['intuition']['value']),
            'eau' => min($traits['forceStat']['value'], $traits['perception']['value']),
            'feu' => min($traits['agilite']['value'], $traits['intelligence']['value']),
            'vide' => $traits['vide']['value'],
        ];

        foreach (self::RINGS as $name => [$x, $y]) {
            $pdf->text((string) $rings[$name], $x, $y + 12, 34, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
        }

        $this->traitBonus($pdf, $traits['vide']['bonus'], self::VOID_BONUS[0], self::VOID_BONUS[1]);
    }

    private function traitBonus(PdfWriter $pdf, int $bonus, int $x, int $y): void
    {
        if ($bonus > 0) {
            $pdf->text('+' . $bonus, $x, $y, 22, ['align' => PdfWriter::ALIGN_CENTER]);
        }
    }

    private function standings(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        foreach (self::STANDINGS as $name => $y) {
            $value = $sheet->{'get' . ucfirst($name)}();

            if ($value !== null) {
                $pdf->text(number_format((float) $value, 1, '.', ''), self::STANDING_RIGHT, $y, 18, ['align' => PdfWriter::ALIGN_RIGHT]);
            }
        }
    }

    private function combat(PdfWriter $pdf, FichePersonnage $sheet, array $traits, int $rank): void
    {
        $reflexes = $traits['reflexes']['value'];
        $earth = min($traits['constitution']['value'], $traits['volonte']['value']);
        $armor = $sheet->getArmure();
        $centered = ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER];

        $initiative = ($reflexes + $rank) . 'g' . $reflexes;
        $modifier = (int) $sheet->getInitiativeModifier();

        if ($modifier !== 0) {
            $initiative .= ($modifier > 0 ? '+' : '') . $modifier;
        }

        [$left, $indent] = self::COMBAT_LABEL;
        [$line1, $line2, $initiativeBaseline] = self::COMBAT_INITIATIVE;
        [$ndLine, $ndBaseline] = self::COMBAT_ND;
        [$diceX, $diceY, $diceSize] = self::COMBAT_DICE;

        $pdf->overlay($this->publicDirectory . self::DICE_ICON, $diceX, $diceY, $diceSize, $diceSize);
        $pdf->text('Réflexes + Rang Général', $indent, $line1, self::COMBAT_LABEL_SIZE);
        $pdf->text('/garder Réflexes', $indent, $line2, self::COMBAT_LABEL_SIZE);
        $pdf->text('Réflexes x 5 + Rang Général', $left, $ndLine, self::COMBAT_LABEL_SIZE);

        $this->combatValue($pdf, $initiative, $initiativeBaseline);
        $this->combatValue($pdf, (string) ($reflexes * 5 + $rank), $ndBaseline);

        if ($armor) {
            $pdf->text('+' . (int) $armor->getNdArmure(), self::COMBAT_ARMOUR[0], self::COMBAT_ARMOUR[1], 30, $centered);
        }

        $reduction = ($armor ? (int) $armor->getReduction() : 0) + (int) $sheet->getReductionModifier();

        if ($reduction > 0) {
            $pdf->text((string) $reduction, self::COMBAT_REDUCTION[0], self::COMBAT_REDUCTION[1], 30, $centered);
        }

        $total = 0;

        for ($i = 0; $i < self::HP_ROWS; $i++) {
            $hp = $i === 0 ? $earth * 5 : $earth * 2;
            $total += $hp;
            $pdf->text((string) $hp, self::HP_COLUMN, self::HP_FIRST + $i * self::HP_STEP, 28, $centered);
        }

        $pdf->text((string) $total, self::HP_COLUMN, self::HP_FIRST + self::HP_ROWS * self::HP_STEP, 28, $centered);

        $pdf->text((string) ($traits['constitution']['value'] * 2 + $rank), self::COMBAT_RECOVERY[0], self::COMBAT_RECOVERY[1], 30, $centered);
    }

    private function combatValue(PdfWriter $pdf, string $value, int $baseline): void
    {
        [$x, $width] = self::COMBAT_VALUE;

        $pdf->text($value, $x, $baseline, self::COMBAT_VALUE_SIZE, [
            'bold' => true,
            'align' => PdfWriter::ALIGN_CENTER,
            'width' => $width,
        ]);
    }

    private function skills(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        foreach (array_slice($this->orderedSkills($sheet), 0, self::SKILL_ROWS) as $row => $skill) {
            $line = self::SKILL_FIRST + $row * self::SKILL_STEP;
            $baseline = $line - 8;
            $skillEntity = $skill['skill'];

            if ($skill['dojo'] > 0) {
                $pdf->text('x', self::SKILL_COLUMNS['dot'], $line - 3, 26, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
            }

            $pdf->text($skillEntity->getNom(), self::SKILL_COLUMNS['name'][0], $baseline, 24, [
                'bold' => true,
                'width' => self::SKILL_COLUMNS['name'][1],
                'ink' => $skill['tainted'] ? PdfWriter::INK_TAINTED : PdfWriter::INK_DEFAULT,
            ]);
            $pdf->text((string) $skill['value'], self::SKILL_COLUMNS['rank'], $baseline - 4, 26, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
            $this->specialisationLine($pdf, $skill['specialisations'], $baseline);

            if ($skill['value'] >= 3) {
                $this->masteryBlock($pdf, $skillEntity->getMastery3Text(), self::SKILL_COLUMNS['mastery3'], $line);
            }

            if ($skill['value'] >= 6) {
                $this->masteryBlock($pdf, $skillEntity->getMastery6Text(), self::SKILL_COLUMNS['mastery6'], $line);
            }
        }
    }

    private function orderedSkills(FichePersonnage $sheet): array
    {
        $skills = [];

        for ($i = 1; $i <= 20; $i++) {
            $skillEntity = $sheet->{'getCompetence' . $i}();

            if ($skillEntity === null) {
                continue;
            }

            $skills[] = [
                'skill' => $skillEntity,
                'value' => (int) $sheet->{'getValeur' . $i}(),
                'dojo' => (int) $sheet->{'getCompEcole' . $i}(),
                'specialisations' => $this->specialisations($skillEntity, (string) $sheet->{'getSpecialisations' . $i}()),
                'tainted' => (bool) $skillEntity->getDegradante(),
            ];
        }

        usort($skills, function (array $a, array $b) {
            return [$a['dojo'] > 0 ? 0 : 1, $this->sortable($a['skill']->getNom())]
                <=> [$b['dojo'] > 0 ? 0 : 1, $this->sortable($b['skill']->getNom())];
        });

        return $skills;
    }

    private function masteryBlock(PdfWriter $pdf, string $text, array $column, int $line): void
    {
        [$x, $width] = $column;
        $written = min($pdf->lineCount($text, self::MASTERY_SIZE, $width), self::MASTERY_LINES);
        $top = self::MASTERY_TOP + (self::MASTERY_LINES - $written) * intdiv(self::MASTERY_LEADING, 2);

        $pdf->block([$text], $x, $line + $top, $width, self::MASTERY_SIZE, self::MASTERY_LEADING, ['lines' => self::MASTERY_LINES]);
    }

    private function specialisationLine(PdfWriter $pdf, array $labels, int $baseline): void
    {
        [$x, $width] = self::SKILL_COLUMNS['spec'];
        $cursor = $x;
        $last = count($labels) - 1;

        foreach ($labels as $index => $label) {
            $segment = $index < $last ? $label . ', ' : $label;
            $span = $pdf->widthOf($segment, self::SPECIALISATION_SIZE);
            $remaining = $width - ($cursor - $x);
            $options = ['ink' => $this->isTainted($label) ? PdfWriter::INK_TAINTED : PdfWriter::INK_DEFAULT];

            if ($span > $remaining) {
                $pdf->text($segment, $cursor, $baseline, self::SPECIALISATION_SIZE, $options + ['width' => $remaining]);

                return;
            }

            $pdf->text($segment, $cursor, $baseline, self::SPECIALISATION_SIZE, $options);
            $cursor += (int) round($span);
        }
    }

    private function isTainted(string $label): bool
    {
        return mb_stripos($label, 'dégradante') !== false;
    }

    private function specialisations(Competence $skillEntity, string $bitmask): array
    {
        $labels = [];

        for ($k = 1; $k <= 6; $k++) {
            $label = $skillEntity->{'getSpecialisation' . $k}();

            if ($label && ($bitmask[$k - 1] ?? '0') === '1') {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    private function weapons(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        $weapons = array_filter($this->orderedItems($sheet), fn ($item) => $item->getCategorie() === self::WEAPON_CATEGORY);

        foreach (array_slice(array_values($weapons), 0, self::WEAPON_ROWS) as $row => $weapon) {
            $baseline = self::WEAPON_FIRST + $row * self::WEAPON_STEP + 26;
            $cells = [
                $weapon->getNom(),
                mb_convert_case((string) $weapon->getTaille(), MB_CASE_TITLE, 'UTF-8'),
                mb_convert_case((string) $weapon->getPoids(), MB_CASE_TITLE, 'UTF-8'),
                (string) $weapon->getVdOuForce(),
            ];

            foreach (self::WEAPON_COLUMNS as $column => [$x, $width, $align]) {
                $pdf->text($cells[$column], $x, $baseline, 22, ['width' => $width, 'align' => $align]);
            }
        }
    }

    private function recalls(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        $blocks = [
            self::RECALL_ADVANTAGE => [$sheet->getAvantage1(), $sheet->getAvantage2()],
            self::RECALL_DISADVANTAGE => [$sheet->getDesavantage1(), $sheet->getDesavantage2()],
        ];

        foreach ($blocks as $first => $items) {
            $row = 0;

            foreach (array_filter($items) as $item) {
                if ($row >= self::RECALL_ROWS) {
                    break;
                }

                $pdf->text($item->getNom(), 1150, $first + $row * self::RECALL_STEP + 28, 24, ['width' => 340]);
                $row++;
            }
        }
    }

    private function personalInfo(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        $values = [
            'sex' => self::SEX_LABELS[$sheet->getPersonnage()->getGenre()] ?? '',
            'age' => (string) $sheet->getAge(),
            'height' => (string) $sheet->getHeight(),
            'weight' => (string) $sheet->getWeight(),
            'hair' => (string) $sheet->getHair(),
            'eyes' => (string) $sheet->getEyes(),
        ];

        foreach (self::IDENTITY_BOXES as $key => [$x, $y]) {
            $pdf->text($values[$key], $x, $y, 21, ['align' => PdfWriter::ALIGN_CENTER, 'width' => 122]);
        }

        [$x, $y, $width] = self::APPEARANCE_BOX;
        $pdf->block([(string) $sheet->getAppearance()], $x, $y, $width, 19, self::APPEARANCE_LEADING, ['lines' => self::APPEARANCE_LINES]);

        [$x, $y, $width] = self::NOTES_BOX;
        $pdf->block([(string) $sheet->getNotes()], $x, $y, $width, 22, self::NOTES_LEADING, ['lines' => self::NOTES_LINES]);
    }

    private function inventory(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        [$x, $width] = self::INVENTORY_TEXT;

        foreach (array_slice($this->orderedItems($sheet), 0, self::INVENTORY_ROWS) as $row => $item) {
            $pdf->text($item->getNom(), $x, self::INVENTORY_FIRST + $row * self::INVENTORY_STEP, 22, ['width' => $width]);
        }
    }

    private function orderedItems(FichePersonnage $sheet): array
    {
        $items = $sheet->getInventoryItems()->toArray();

        usort($items, function ($a, $b) {
            return [self::CATEGORY_ORDER[$a->getCategorie()] ?? count(self::CATEGORY_ORDER), $this->sortable($a->getNom())]
                <=> [self::CATEGORY_ORDER[$b->getCategorie()] ?? count(self::CATEGORY_ORDER), $this->sortable($b->getNom())];
        });

        return $items;
    }

    private function purse(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        foreach (self::PURSE_COLUMNS as $coin => $x) {
            $amount = (int) $sheet->{'get' . ucfirst($coin)}();

            if ($amount > 0) {
                $pdf->text((string) $amount, $x, self::PURSE_BASELINE, 36, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
            }
        }
    }

    private function details(PdfWriter $pdf, FichePersonnage $sheet): void
    {
        $blocks = [
            [self::DETAIL_ADVANTAGE, [$sheet->getAvantage1(), $sheet->getAvantage2()]],
            [self::DETAIL_DISADVANTAGE, [$sheet->getDesavantage1(), $sheet->getDesavantage2()]],
        ];

        foreach ($blocks as [[$top, $rows], $items]) {
            $items = array_values(array_filter($items));
            $row = 0;

            foreach ($items as $index => $item) {
                $budget = (int) floor(($rows - $row) / (count($items) - $index));

                if ($budget < 1) {
                    break;
                }

                $baseline = $top + $row * self::DETAIL_STEP + 29;
                $pdf->text($item->getNom(), self::DETAIL_TEXT[0], $baseline, 24, ['bold' => true, 'width' => self::DETAIL_TEXT[1]]);
                $pdf->text((string) $this->xpRanker->advantageCost($sheet, $item), self::DETAIL_POINTS, $baseline, 22, ['align' => PdfWriter::ALIGN_RIGHT]);

                $pdf->block(
                    [$item->getSummary() ?: (string) $item->getDescription()],
                    self::DETAIL_TEXT[0],
                    $baseline + self::DETAIL_STEP,
                    self::DETAIL_TEXT[1] + 60,
                    19,
                    self::DETAIL_STEP,
                    ['lines' => $budget - 1]
                );

                $row += $budget;
            }
        }
    }

    private function techniques(PdfWriter $pdf, FichePersonnage $sheet, int $rank): void
    {
        $character = $sheet->getPersonnage();
        $dojo = $character->getEcole();
        $byRing = $dojo === null || !$dojo->getTech1Nom();

        [$x, $y] = self::OVERLAY_BOX;
        $pdf->overlay($this->sheet($byRing ? self::RINGS_OVERLAY : self::TECHNIQUES_OVERLAY), $x, $y);

        if ($dojo === null) {
            return;
        }

        if ($byRing) {
            $this->fillBlock($pdf, 0, [$dojo->getTechSpecialSummary() ?: $dojo->getTechSpecialDesc()]);

            $affinity = $this->sortable((string) $dojo->getAffinite());
            $deficiency = $this->sortable((string) $dojo->getDeficience());

            foreach (array_keys(self::RING_BADGES) as $index => $ring) {
                $this->fillBlock($pdf, $index + 1, $this->spellNames($sheet, [$ring]));
                $this->ringBadge($pdf, $ring, $affinity, $deficiency);
            }

            return;
        }

        $kihos = $this->spellNamesOf($sheet, 'KIHO');
        $tattoos = $this->spellNamesOf($sheet, 'TATOUAGE');

        for ($i = 1; $i <= 5; $i++) {
            $footers = isset($kihos[$i - 1]) ? ['[KIHO] ' . $kihos[$i - 1]] : [];

            if ($i % 2 === 1) {
                foreach (array_slice($tattoos, $i - 1, self::TATTOOS_PER_RANK) as $tattoo) {
                    $footers[] = '[TATOUAGE] ' . $tattoo;
                }
            }

            $this->fillBlock(
                $pdf,
                $i - 1,
                [$dojo->{'getTech' . $i . 'Summary'}() ?: $dojo->{'getTech' . $i . 'Desc'}()],
                $i > $rank,
                $footers
            );
        }
    }

    private function ringBadge(PdfWriter $pdf, string $ring, string $affinity, string $deficiency): void
    {
        $element = $this->sortable($ring);

        if ($element === $affinity) {
            $label = 'AFFINITÉ';
            $ink = PdfWriter::INK_AFFINITY;
        } elseif ($element === $deficiency) {
            $label = 'DÉFICIENCE';
            $ink = PdfWriter::INK_TAINTED;
        } else {
            return;
        }

        $pdf->text($label, self::RING_BADGE_RIGHT, self::RING_BADGES[$ring], 20, [
            'bold' => true,
            'align' => PdfWriter::ALIGN_RIGHT,
            'ink' => $ink,
        ]);
    }

    private function spellNames(FichePersonnage $sheet, array $rings): array
    {
        $names = [];

        foreach ($sheet->getKnownSpells() as $spell) {
            if ($spell->getCategorie() !== 'KIHO' && in_array($spell->getAnneau(), $rings, true)) {
                $names[] = $spell->getNom();
            }
        }

        return $names;
    }

    private function spellNamesOf(FichePersonnage $sheet, string $category): array
    {
        $names = [];

        foreach ($sheet->getKnownSpells() as $spell) {
            if ($spell->getCategorie() === $category) {
                $names[] = $spell->getNom();
            }
        }

        usort($names, fn ($a, $b) => strcmp($this->sortable($a), $this->sortable($b)));

        return $names;
    }

    private function fillBlock(PdfWriter $pdf, int $index, array $lines, bool $locked = false, array $footers = []): void
    {
        $lines = array_values(array_filter($lines, static fn ($line) => trim((string) $line) !== ''));

        if ($lines === [] && $footers === []) {
            return;
        }

        $baselines = self::OVERLAY_BLOCKS[$index];
        $footers = array_slice($footers, 0, count($baselines));
        $options = ['ink' => $locked ? PdfWriter::INK_MUTED : PdfWriter::INK_DEFAULT];
        $written = $pdf->block(
            $lines,
            self::OVERLAY_TEXT[0],
            $baselines[0],
            self::OVERLAY_TEXT[1],
            self::OVERLAY_SIZE,
            $baselines[1] - $baselines[0],
            $options + ['lines' => count($baselines) - count($footers)]
        );

        foreach ($footers as $k => $footer) {
            $pdf->text($footer, self::OVERLAY_TEXT[0], $baselines[$written + $k], self::OVERLAY_SIZE, $options + [
                'bold' => true,
                'width' => self::OVERLAY_TEXT[1],
            ]);
        }
    }

    private function sortable(string $name): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        return strtolower($ascii === false ? $name : $ascii);
    }
}
