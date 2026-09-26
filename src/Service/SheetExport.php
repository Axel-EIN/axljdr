<?php

namespace App\Service;

use App\Entity\Competence;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;

class SheetExport
{
    private const CANVAS_WIDTH = 1654;
    private const SHEETS = '/assets/sheets/';
    private const RECTO = 'Character Sheet Recto Base.png';
    private const VERSO = 'Character Sheet Verso Base.png';
    private const FRAME = 'Cadre Illu.png';
    private const RINGS_OVERLAY = 'Character Sheet Verso Elements.png';
    private const TECHNIQUES_OVERLAY = 'Character Sheet Verso Techniques.png';

    private const ILLUSTRATION = [1136, 45, 472, 548];
    private const ILLUSTRATION_FOCUS = 0.1;
    private const FRAME_BOX = [1123, 28, 503, 585];

    private const IDENTITY = [
        'nom' => [166, 61, 360],
        'clan' => [166, 110, 360],
        'ecole' => [166, 161, 360],
        'rang' => [801, 57, 280],
        'exp' => [801, 107, 85],
        'reste' => [987, 107, 95],
        'joueur' => [801, 157, 280],
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

    private const STANDING_RIGHT = 1116;
    private const STANDINGS = ['honneur' => 684, 'gloire' => 716, 'infamie' => 750, 'statut' => 782];

    private const COMBAT_INITIATIVE = [1556, 900];
    private const COMBAT_ND = [1556, 985];
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
        'nom' => [82, 258],
        'rang' => 392,
        'spe' => [445, 181],
        'cap3' => [648, 218],
        'cap6' => [888, 217],
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
        'sexe' => [234, 115],
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
    private ClasseurXP $classeurXP;

    public function __construct(string $publicDirectory, ClasseurXP $classeurXP)
    {
        $this->publicDirectory = $publicDirectory;
        $this->classeurXP = $classeurXP;
    }

    public function build(FichePersonnage $fiche): string
    {
        $personnage = $fiche->getPersonnage();
        $traits = $this->traits($fiche);
        $rang = $this->classeurXP->rank($this->classeurXP->total($personnage));
        $pdf = new PdfWriter(self::CANVAS_WIDTH);

        $pdf->background($this->sheet(self::RECTO));
        $this->identity($pdf, $fiche, $rang);
        $this->illustration($pdf, $personnage);
        $this->traitCircles($pdf, $traits);
        $this->standings($pdf, $fiche);
        $this->combat($pdf, $fiche, $traits, $rang);
        $this->skills($pdf, $fiche);
        $this->weapons($pdf, $fiche);
        $this->recalls($pdf, $fiche);

        $pdf->newPage();
        $pdf->background($this->sheet(self::VERSO));
        $this->personalInfo($pdf, $fiche);
        $this->inventory($pdf, $fiche);
        $this->purse($pdf, $fiche);
        $this->details($pdf, $fiche);
        $this->techniques($pdf, $fiche, $rang);

        return $pdf->render();
    }

    public function filename(FichePersonnage $fiche): string
    {
        $personnage = $fiche->getPersonnage();
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', trim($personnage->getNom() . ' ' . $personnage->getPrenom()));
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $ascii === false ? 'personnage' : $ascii) ?? '');

        return 'fiche-' . trim($slug, '-') . '.pdf';
    }

    private function sheet(string $name): string
    {
        return $this->publicDirectory . self::SHEETS . $name;
    }

    private function traits(FichePersonnage $fiche): array
    {
        $personnage = $fiche->getPersonnage();
        $bonuses = array_filter([
            $personnage->getFamille() ? $personnage->getFamille()->getBonusStatNom() : null,
            $personnage->getEcole() ? $personnage->getEcole()->getBonusStatNom() : null,
        ]);

        $traits = [];

        foreach (['constitution', 'volonte', 'reflexes', 'intuition', 'agilite', 'intelligence', 'forceStat', 'perception', 'vide'] as $name) {
            $bonus = count(array_keys($bonuses, $name, true));
            $traits[$name] = ['value' => (int) $fiche->{'get' . ucfirst($name)}() + $bonus, 'bonus' => $bonus];
        }

        return $traits;
    }

    private function identity(PdfWriter $pdf, FichePersonnage $fiche, int $rang): void
    {
        $personnage = $fiche->getPersonnage();

        $values = [
            'nom' => trim($personnage->getPrenom() . ' ' . $personnage->getNom()),
            'clan' => $personnage->getClan() ? $personnage->getClan()->getNom() : '',
            'ecole' => $personnage->getEcole() ? $personnage->getEcole()->getNom() : '',
            'rang' => (string) $rang,
            'exp' => (string) $this->classeurXP->earned($fiche),
            'reste' => (string) $this->classeurXP->remaining($fiche),
            'joueur' => $personnage->getJoueur() ? $personnage->getJoueur()->getPseudo() : '',
        ];

        foreach (self::IDENTITY as $key => [$x, $y, $width]) {
            $pdf->text($values[$key], $x, $y, 28, ['bold' => true, 'width' => $width]);
        }
    }

    private function illustration(PdfWriter $pdf, Personnage $personnage): void
    {
        if ($personnage->getIllustration()) {
            [$x, $y, $width, $height] = self::ILLUSTRATION;
            $pdf->cover($this->publicDirectory . '/' . ltrim($personnage->getIllustration(), '/'), $x, $y, $width, $height, self::ILLUSTRATION_FOCUS);
        }

        [$x, $y, $width, $height] = self::FRAME_BOX;
        $pdf->overlay($this->sheet(self::FRAME), $x, $y, $width, $height);
    }

    private function traitCircles(PdfWriter $pdf, array $traits): void
    {
        foreach (self::TRAITS as $name => [$x, $y, $labelX, $labelY]) {
            $pdf->text((string) $traits[$name]['value'], $x, $y + 12, 34, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);

            if ($traits[$name]['bonus'] > 0) {
                $pdf->text('+' . $traits[$name]['bonus'], $labelX, $labelY, 22, ['align' => PdfWriter::ALIGN_CENTER]);
            }
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
    }

    private function standings(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        foreach (self::STANDINGS as $name => $y) {
            $value = $fiche->{'get' . ucfirst($name)}();

            if ($value !== null) {
                $pdf->text(number_format((float) $value, 1, '.', ''), self::STANDING_RIGHT, $y, 18, ['align' => PdfWriter::ALIGN_RIGHT]);
            }
        }
    }

    private function combat(PdfWriter $pdf, FichePersonnage $fiche, array $traits, int $rang): void
    {
        $reflexes = $traits['reflexes']['value'];
        $terre = min($traits['constitution']['value'], $traits['volonte']['value']);
        $armure = $fiche->getArmure();
        $centered = ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER];

        $initiative = ($reflexes + $rang) . 'g' . $reflexes;
        $modifier = (int) $fiche->getInitiativeModifier();

        if ($modifier !== 0) {
            $initiative .= ($modifier > 0 ? '+' : '') . $modifier;
        }

        $pdf->text($initiative, self::COMBAT_INITIATIVE[0], self::COMBAT_INITIATIVE[1], 32, $centered);
        $pdf->text((string) (5 + $reflexes * 5 + (int) $fiche->getNdModifier()), self::COMBAT_ND[0], self::COMBAT_ND[1], 30, $centered);

        if ($armure) {
            $pdf->text('+' . (int) $armure->getNdArmure(), self::COMBAT_ARMOUR[0], self::COMBAT_ARMOUR[1], 30, $centered);
        }

        $reduction = ($armure ? (int) $armure->getReduction() : 0) + (int) $fiche->getReductionModifier();

        if ($reduction > 0) {
            $pdf->text((string) $reduction, self::COMBAT_REDUCTION[0], self::COMBAT_REDUCTION[1], 30, $centered);
        }

        $total = 0;

        for ($i = 0; $i < self::HP_ROWS; $i++) {
            $hp = $i === 0 ? $terre * 5 : $terre * 2;
            $total += $hp;
            $pdf->text((string) $hp, self::HP_COLUMN, self::HP_FIRST + $i * self::HP_STEP, 28, $centered);
        }

        $pdf->text((string) $total, self::HP_COLUMN, self::HP_FIRST + self::HP_ROWS * self::HP_STEP, 28, $centered);

        $pdf->text((string) ($traits['constitution']['value'] * 2 + $rang), self::COMBAT_RECOVERY[0], self::COMBAT_RECOVERY[1], 30, $centered);
    }

    private function skills(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        foreach (array_slice($this->orderedSkills($fiche), 0, self::SKILL_ROWS) as $row => $skill) {
            $line = self::SKILL_FIRST + $row * self::SKILL_STEP;
            $baseline = $line - 8;
            $competence = $skill['competence'];

            if ($skill['ecole'] > 0) {
                $pdf->text('x', self::SKILL_COLUMNS['dot'], $line - 3, 26, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
            }

            $pdf->text($competence->getNom(), self::SKILL_COLUMNS['nom'][0], $baseline, 24, [
                'bold' => true,
                'width' => self::SKILL_COLUMNS['nom'][1],
                'ink' => $skill['degradante'] ? PdfWriter::INK_TAINTED : PdfWriter::INK_DEFAULT,
            ]);
            $pdf->text((string) $skill['valeur'], self::SKILL_COLUMNS['rang'], $baseline - 4, 26, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
            $this->specialisationLine($pdf, $skill['specialisations'], $baseline);

            if ($skill['valeur'] >= 3) {
                $this->masteryBlock($pdf, $competence->getMastery3Summary() ?: (string) $competence->getCapacite(), self::SKILL_COLUMNS['cap3'], $line);
            }

            if ($skill['valeur'] >= 6) {
                $this->masteryBlock($pdf, $competence->getMastery6Summary() ?: (string) $competence->getCapacite2(), self::SKILL_COLUMNS['cap6'], $line);
            }
        }
    }

    private function orderedSkills(FichePersonnage $fiche): array
    {
        $skills = [];

        for ($i = 1; $i <= 20; $i++) {
            $competence = $fiche->{'getCompetence' . $i}();

            if ($competence === null) {
                continue;
            }

            $skills[] = [
                'competence' => $competence,
                'valeur' => (int) $fiche->{'getValeur' . $i}(),
                'ecole' => (int) $fiche->{'getCompEcole' . $i}(),
                'specialisations' => $this->specialisations($competence, (string) $fiche->{'getSpecialisations' . $i}()),
                'degradante' => (bool) $competence->getDegradante(),
            ];
        }

        usort($skills, function (array $a, array $b) {
            return [$a['ecole'] > 0 ? 0 : 1, $this->sortable($a['competence']->getNom())]
                <=> [$b['ecole'] > 0 ? 0 : 1, $this->sortable($b['competence']->getNom())];
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
        [$x, $width] = self::SKILL_COLUMNS['spe'];
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

    private function specialisations(Competence $competence, string $bitmask): array
    {
        $labels = [];

        for ($k = 1; $k <= 6; $k++) {
            $label = $competence->{'getSpecialisation' . $k}();

            if ($label && ($bitmask[$k - 1] ?? '0') === '1') {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    private function weapons(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        $weapons = array_filter($this->orderedItems($fiche), fn ($objet) => $objet->getCategorie() === self::WEAPON_CATEGORY);

        foreach (array_slice(array_values($weapons), 0, self::WEAPON_ROWS) as $row => $weapon) {
            $baseline = self::WEAPON_FIRST + $row * self::WEAPON_STEP + 26;
            $cells = [
                $weapon->getNom(),
                mb_convert_case((string) $weapon->getTaille(), MB_CASE_TITLE, 'UTF-8'),
                mb_convert_case((string) $weapon->getPoids(), MB_CASE_TITLE, 'UTF-8'),
                (string) $weapon->getVd(),
            ];

            foreach (self::WEAPON_COLUMNS as $column => [$x, $width, $align]) {
                $pdf->text($cells[$column], $x, $baseline, 22, ['width' => $width, 'align' => $align]);
            }
        }
    }

    private function recalls(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        $blocks = [
            self::RECALL_ADVANTAGE => [$fiche->getAvantage1(), $fiche->getAvantage2()],
            self::RECALL_DISADVANTAGE => [$fiche->getDesavantage1(), $fiche->getDesavantage2()],
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

    private function personalInfo(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        $values = [
            'sexe' => self::SEX_LABELS[$fiche->getPersonnage()->getGenre()] ?? '',
            'age' => (string) $fiche->getAge(),
            'height' => (string) $fiche->getHeight(),
            'weight' => (string) $fiche->getWeight(),
            'hair' => (string) $fiche->getHair(),
            'eyes' => (string) $fiche->getEyes(),
        ];

        foreach (self::IDENTITY_BOXES as $key => [$x, $y]) {
            $pdf->text($values[$key], $x, $y, 21, ['align' => PdfWriter::ALIGN_CENTER, 'width' => 122]);
        }

        [$x, $y, $width] = self::APPEARANCE_BOX;
        $pdf->block([(string) $fiche->getAppearance()], $x, $y, $width, 19, self::APPEARANCE_LEADING, ['lines' => self::APPEARANCE_LINES]);

        [$x, $y, $width] = self::NOTES_BOX;
        $pdf->block([(string) $fiche->getNotes()], $x, $y, $width, 22, self::NOTES_LEADING, ['lines' => self::NOTES_LINES]);
    }

    private function inventory(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        [$x, $width] = self::INVENTORY_TEXT;

        foreach (array_slice($this->orderedItems($fiche), 0, self::INVENTORY_ROWS) as $row => $objet) {
            $pdf->text($objet->getNom(), $x, self::INVENTORY_FIRST + $row * self::INVENTORY_STEP, 22, ['width' => $width]);
        }
    }

    private function orderedItems(FichePersonnage $fiche): array
    {
        $items = $fiche->getInventoryItems()->toArray();

        usort($items, function ($a, $b) {
            return [self::CATEGORY_ORDER[$a->getCategorie()] ?? count(self::CATEGORY_ORDER), $this->sortable($a->getNom())]
                <=> [self::CATEGORY_ORDER[$b->getCategorie()] ?? count(self::CATEGORY_ORDER), $this->sortable($b->getNom())];
        });

        return $items;
    }

    private function purse(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        foreach (self::PURSE_COLUMNS as $coin => $x) {
            $amount = (int) $fiche->{'get' . ucfirst($coin)}();

            if ($amount > 0) {
                $pdf->text((string) $amount, $x, self::PURSE_BASELINE, 36, ['bold' => true, 'align' => PdfWriter::ALIGN_CENTER]);
            }
        }
    }

    private function details(PdfWriter $pdf, FichePersonnage $fiche): void
    {
        $blocks = [
            [self::DETAIL_ADVANTAGE, [$fiche->getAvantage1(), $fiche->getAvantage2()]],
            [self::DETAIL_DISADVANTAGE, [$fiche->getDesavantage1(), $fiche->getDesavantage2()]],
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
                $pdf->text((string) $this->classeurXP->advantageCost($fiche, $item), self::DETAIL_POINTS, $baseline, 22, ['align' => PdfWriter::ALIGN_RIGHT]);

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

    private function techniques(PdfWriter $pdf, FichePersonnage $fiche, int $rang): void
    {
        $personnage = $fiche->getPersonnage();
        $ecole = $personnage->getEcole();
        $byRing = $ecole === null || !$ecole->getTech1Nom();

        [$x, $y] = self::OVERLAY_BOX;
        $pdf->overlay($this->sheet($byRing ? self::RINGS_OVERLAY : self::TECHNIQUES_OVERLAY), $x, $y);

        if ($ecole === null) {
            return;
        }

        if ($byRing) {
            $this->fillBlock($pdf, 0, [$ecole->getTechSpecialSummary() ?: $ecole->getTechSpecialDesc()]);

            $affinity = $this->sortable((string) $ecole->getAffinite());
            $deficiency = $this->sortable((string) $ecole->getDeficience());

            foreach (array_keys(self::RING_BADGES) as $index => $ring) {
                $this->fillBlock($pdf, $index + 1, $this->spellNames($fiche, [$ring]));
                $this->ringBadge($pdf, $ring, $affinity, $deficiency);
            }

            return;
        }

        $kihos = $this->spellNamesOf($fiche, 'KIHO');
        $tattoos = $this->spellNamesOf($fiche, 'TATOUAGE');

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
                [$ecole->{'getTech' . $i . 'Summary'}() ?: $ecole->{'getTech' . $i . 'Desc'}()],
                $i > $rang,
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

    private function spellNames(FichePersonnage $fiche, array $rings): array
    {
        $names = [];

        foreach ($fiche->getKnownSpells() as $spell) {
            if ($spell->getCategorie() !== 'KIHO' && in_array($spell->getAnneau(), $rings, true)) {
                $names[] = $spell->getNom();
            }
        }

        return $names;
    }

    private function spellNamesOf(FichePersonnage $fiche, string $categorie): array
    {
        $names = [];

        foreach ($fiche->getKnownSpells() as $spell) {
            if ($spell->getCategorie() === $categorie) {
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
