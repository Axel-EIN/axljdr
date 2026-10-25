<?php

namespace App\Service;

use App\Entity\Avantage;
use App\Entity\FactionType;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Entity\Status;
use App\Repository\DevelopmentRepository;

class CharacterMarkdown
{
    private const RINGS = [
        'terre' => ['Terre', ['constitution' => 'Constitution', 'volonte' => 'Volonté']],
        'air' => ['Air', ['reflexes' => 'Réflexes', 'intuition' => 'Intuition']],
        'eau' => ['Eau', ['forceStat' => 'Force', 'perception' => 'Perception']],
        'feu' => ['Feu', ['agilite' => 'Agilité', 'intelligence' => 'Intelligence']],
        'vide' => ['Vide', []],
    ];
    private const STANDINGS = ['honneur' => 'Honneur', 'gloire' => 'Gloire', 'statut' => 'Statut', 'infamie' => 'Infamie', 'souillure' => 'Souillure'];
    private const SPELL_CATEGORIES = ['MAGIE' => 'Sort', 'KIHO' => 'Kiho', 'TATOUAGE' => 'Tatouage'];
    private const ITEM_CATEGORIES = ['OBJET' => 'objet', 'ARME' => 'arme', 'PROJECTILE' => 'projectile', 'ARMURE' => 'armure'];
    private const COINS = ['koku' => 'koku', 'bu' => 'bu', 'zeni' => 'zeni'];
    private const FREE_POSITION = 'En déplacement';

    private XpRanker $xpRanker;
    private SheetReader $reader;
    private HistoryRanker $historyRanker;
    private Locator $locator;
    private DevelopmentRepository $developmentRepository;

    public function __construct(XpRanker $xpRanker, SheetReader $reader, HistoryRanker $historyRanker, Locator $locator, DevelopmentRepository $developmentRepository)
    {
        $this->xpRanker = $xpRanker;
        $this->reader = $reader;
        $this->historyRanker = $historyRanker;
        $this->locator = $locator;
        $this->developmentRepository = $developmentRepository;
    }

    public function build(Personnage $character): string
    {
        $sheet = $character->getFichePersonnage();
        $rank = $this->xpRanker->rank($this->xpRanker->total($character));

        $blocks = [
            '# ' . $character->getNomComplet(),
            "> Export AXL-JDR — personnage de L5R (Rokugan), généré le " . date('d/m/Y') . ".\n"
                . "> Ce document décrit un personnage de jeu de rôle, pour servir de contexte à une IA.",
            $this->section('Identité', $this->identity($character, $sheet, $rank)),
        ];

        if ($sheet !== null) {
            $blocks[] = $this->section('Apparence', $this->appearance($sheet));
            $blocks[] = $this->section('Caractéristiques', $this->characteristics($character, $sheet));
            $blocks[] = $this->section('Statut social', $this->standings($sheet));
            $blocks[] = $this->section('Combat', $this->combat($sheet, $rank));
            $blocks[] = $this->section('Compétences', $this->skills($sheet));
            $blocks[] = $this->section('Avantages', $this->advantages($sheet, [$sheet->getAvantage1(), $sheet->getAvantage2()], ''));
            $blocks[] = $this->section('Désavantages', $this->advantages($sheet, [$sheet->getDesavantage1(), $sheet->getDesavantage2()], '+'));
            $blocks[] = $this->section("Techniques d'école", $this->techniques($character, $rank));
            $blocks[] = $this->section('Sorts, kiho et tatouages', $this->spells($sheet));
            $blocks[] = $this->section('Inventaire', $this->inventory($sheet));
        }

        $blocks[] = $this->section('Profil', $this->profile($character, $sheet));
        $blocks[] = $this->section('Historique', $this->history($character));

        return implode("\n\n", array_filter($blocks)) . "\n";
    }

    public function filename(Personnage $character): string
    {
        return $this->reader->slug($character) . '.md';
    }

    private function identity(Personnage $character, ?FichePersonnage $sheet, int $rank): array
    {
        $clan = $character->getClan();
        $type = $character->getEstPj() ? 'PJ' : 'PNJ';

        if ($character->getJoueur() !== null) {
            $type .= ' — joué par ' . $character->getJoueur()->getPseudo();
        }

        return array_filter([
            $this->field('Prénom', $character->getPrenom()),
            $this->field('Nom de famille', $character->getNom()),
            $this->field('Titres', $character->getTitres()),
            $this->field('Type', $type),
            $this->field('Statut', Status::LABELS[$character->getStatus()] ?? null),
            $this->field('Genre', SheetReader::GENDERS[$character->getGenre()] ?? $character->getGenre()),
            $this->field('Faction', $clan ? $clan->getNom() . ' (' . (FactionType::LABELS[$clan->getType()] ?? '') . ')' : null),
            $this->field('Famille', $character->getFamille()?->getNom()),
            $this->field('Classe', $character->getClasse()?->getNom()),
            $this->field('École', $character->getEcole()?->getNom()),
            $this->field('Rang', $sheet !== null ? (string) $rank : null),
            $this->field('XP', $sheet !== null ? $this->xp($sheet) : null),
            $this->field('Position', $this->position($character)),
        ]);
    }

    private function xp(FichePersonnage $sheet): string
    {
        $xp = $this->xpRanker->sheet($sheet);

        return sprintf(
            '%d au total — création %d, désavantages %d, progression %d, dépensés %d, restants %d',
            $xp['total'], $xp['creation'], $xp['disadvantages'], $xp['progression'], $xp['spent'], $xp['remaining']
        );
    }

    private function position(Personnage $character): ?string
    {
        if ($this->locator->freePosition($character) !== null) {
            return self::FREE_POSITION;
        }

        return $this->locator->position($character)?->getNom();
    }

    private function appearance(FichePersonnage $sheet): array
    {
        $measures = array_filter([
            $this->inline('Âge', $sheet->getAge()),
            $this->inline('Taille', $sheet->getHeight()),
            $this->inline('Poids', $sheet->getWeight()),
        ]);
        $looks = array_filter([
            $this->inline('Cheveux', $sheet->getHair()),
            $this->inline('Yeux', $sheet->getEyes()),
        ]);

        $lines = array_filter([
            $measures ? '- ' . implode(' — ', $measures) : null,
            $looks ? '- ' . implode(' — ', $looks) : null,
        ]);
        $text = $this->paragraph($sheet->getAppearance());

        if ($text !== null) {
            $lines[] = ($lines ? "\n" : '') . $text;
        }

        return $lines;
    }

    private function characteristics(Personnage $character, FichePersonnage $sheet): array
    {
        $traits = $this->reader->traits($sheet);
        $rings = $this->reader->rings($traits);
        $lines = ['| Anneau | Valeur | Traits |', '|---|---|---|'];

        foreach (self::RINGS as $ring => [$label, $ringTraits]) {
            $details = [];

            foreach ($ringTraits as $trait => $traitLabel) {
                $details[] = $traitLabel . ' ' . $this->traitValue($traits[$trait]);
            }

            $value = $ring === 'vide' ? $this->traitValue($traits['vide']) : (string) $rings[$ring];
            $lines[] = '| ' . $label . ' | ' . $value . ' | ' . ($details ? implode(', ', $details) : '—') . ' |';
        }

        $dojo = $character->getEcole();
        $affinity = array_filter([
            $this->inline('Affinité', $dojo?->getAffinite()),
            $this->inline('Déficience', $dojo?->getDeficience()),
        ]);

        if ($affinity) {
            $lines[] = '';
            $lines[] = '- ' . implode(' — ', $affinity);
        }

        return $lines;
    }

    private function traitValue(array $trait): string
    {
        return $trait['value'] . ($trait['bonus'] > 0 ? ' (dont +' . $trait['bonus'] . ' de bonus)' : '');
    }

    private function standings(FichePersonnage $sheet): array
    {
        $values = [];

        foreach (self::STANDINGS as $name => $label) {
            $value = $sheet->{'get' . ucfirst($name)}();

            if ($value !== null) {
                $values[] = $this->inline($label, number_format((float) $value, 1, '.', ''));
            }
        }

        return $values ? ['- ' . implode(' — ', $values)] : [];
    }

    private function combat(FichePersonnage $sheet, int $rank): array
    {
        $weapon = $sheet->getArmeActuelle();
        $armor = $sheet->getArmure();
        $armorTn = $this->reader->armorTn($sheet, $rank);
        $weaponLine = null;
        $armorLine = null;

        if ($weapon !== null) {
            $weaponLine = $weapon->getNom()
                . ($weapon->getVdOuForce() ? ' (VD ' . $weapon->getVdOuForce() . ')' : '')
                . ($sheet->getCompCombatActuelle() ? ' — compétence ' . $sheet->getCompCombatActuelle()->getNom() : '');
        }

        if ($armor !== null) {
            $armorLine = $armor->getNom() . ' (ND +' . (int) $armor->getNdArmure() . ', réduction ' . $this->reader->reduction($sheet) . ')';
        }

        return array_filter([
            $this->field('Initiative', $this->reader->initiative($sheet, $rank)),
            $this->field("ND d'armure", $armor !== null ? ($armorTn + (int) $armor->getNdArmure()) . ' (base ' . $armorTn . ')' : (string) $armorTn),
            $this->field('Points de vie', (string) $sheet->getPvMax()),
            $this->field('Récupération', (string) $this->reader->recovery($sheet, $rank)),
            $this->field('Arme en main', $weaponLine),
            $this->field('Armure', $armorLine),
            $this->field("Modificateur d'attaque", $sheet->getAttaqueModifier()),
            $this->field('Modificateur de dégâts', $sheet->getDmgModifier()),
        ]);
    }

    private function skills(FichePersonnage $sheet): array
    {
        $skills = $this->reader->skills($sheet);

        if ($skills === []) {
            return [];
        }

        $lines = ['| Compétence | Trait | Rang | École | Spécialisations |', '|---|---|---|---|---|'];

        foreach ($skills as $skill) {
            $lines[] = sprintf(
                '| %s%s | %s | %d | %s | %s |',
                $skill['skill']->getNom(),
                $skill['tainted'] ? ' (dégradante)' : '',
                $skill['skill']->getTraitCourt(),
                $skill['value'],
                $skill['dojo'] > 0 ? 'oui' : 'non',
                $skill['specialisations'] ? implode(', ', $skill['specialisations']) : '—'
            );
        }

        return $lines;
    }

    private function advantages(FichePersonnage $sheet, array $advantages, string $sign): array
    {
        return array_map(function (Avantage $advantage) use ($sheet, $sign) {
            return '- **' . $advantage->getNom() . '** (' . $sign . $this->xpRanker->advantageCost($sheet, $advantage) . ' XP) : '
                . $this->line($advantage->getSummary() ?: $advantage->getDescription());
        }, array_values(array_filter($advantages)));
    }

    private function techniques(Personnage $character, int $rank): array
    {
        $dojo = $character->getEcole();

        if ($dojo === null) {
            return [];
        }

        $lines = [];

        for ($i = 1; $i <= 5; $i++) {
            $name = $dojo->{'getTech' . $i . 'Nom'}();

            if ($name) {
                $lines[] = $i . '. **' . $name . '**' . ($i > $rank ? ' (non acquise)' : '') . ' : '
                    . $this->line($dojo->{'getTech' . $i . 'Summary'}() ?: $dojo->{'getTech' . $i . 'Desc'}());
            }
        }

        if ($dojo->getTechSpecialNom()) {
            $lines[] = '- **' . $dojo->getTechSpecialNom() . '** (technique spéciale) : '
                . $this->line($dojo->getTechSpecialSummary() ?: $dojo->getTechSpecialDesc());
        }

        return $lines;
    }

    private function spells(FichePersonnage $sheet): array
    {
        $spells = $sheet->getKnownSpells()->toArray();

        usort($spells, fn ($a, $b) => [$a->getCategorie(), (int) $a->getNiveau(), $this->reader->sortable($a->getNom())]
            <=> [$b->getCategorie(), (int) $b->getNiveau(), $this->reader->sortable($b->getNom())]);

        return array_map(function ($spell) {
            $details = array_filter([
                self::SPELL_CATEGORIES[$spell->getCategorie()] ?? $spell->getCategorie(),
                $spell->getAnneau(),
                $spell->getNiveau() !== null ? 'maîtrise ' . $spell->getNiveau() : null,
            ]);

            return '- **' . $spell->getNom() . '** — ' . implode(', ', $details);
        }, $spells);
    }

    private function inventory(FichePersonnage $sheet): array
    {
        $lines = array_map(function ($item) {
            return '- **' . $item->getNom() . '** (' . (self::ITEM_CATEGORIES[$item->getCategorie()] ?? $item->getCategorie()) . ')';
        }, $this->reader->items($sheet));

        $purse = [];

        foreach (self::COINS as $coin => $label) {
            $amount = (int) $sheet->{'get' . ucfirst($coin)}();

            if ($amount > 0) {
                $purse[] = $amount . ' ' . $label;
            }
        }

        if ($purse) {
            $lines[] = $this->field('Bourse', implode(', ', $purse));
        }

        return $lines;
    }

    private function profile(Personnage $character, ?FichePersonnage $sheet): array
    {
        $developments = array_map(function ($development) {
            $scene = $development->getScene();
            $episode = $scene->getEpisodeParent();

            return '- **' . $scene->getTitre() . '** (' . $episode->getChapitreParent()->getTitre()
                . ' — EP #' . $episode->getNumeroSaison() . ' - Scène ' . $scene->getNumero() . ')'
                . ($development->getEstMort() ? ', MORT' : '') . ' : ' . $this->line($development->getContent());
        }, $this->developmentRepository->findByPersonnage($character));

        $parts = [
            'Histoire' => $this->paragraph($character->getDescription()),
            'Développements' => $developments ? implode("\n", $developments) : null,
            'Notes du joueur' => $this->paragraph($character->getPlayerNotes()),
            'Notes de la fiche' => $this->paragraph($sheet?->getNotes()),
            'Notes du MJ' => $this->paragraph($character->getGmNotes()),
        ];

        $lines = [];

        foreach (array_filter($parts) as $title => $content) {
            $lines[] = ($lines ? "\n" : '') . '### ' . $title . "\n\n" . $content;
        }

        return $lines;
    }

    private function history(Personnage $character): array
    {
        $lines = [];
        $season = null;

        foreach ($this->historyRanker->forCharacter($character) as $block) {
            $episode = $block['episode'];
            $chapter = $episode->getChapitreParent();

            if ($chapter->getSaisonParent() !== $season) {
                $season = $chapter->getSaisonParent();
                $lines[] = '- **Saison ' . $season->getNumero() . ' — ' . $season->getTitre() . '**';
            }

            $dead = array_filter($block['lines'], fn (array $line) => $line['dead']);
            $xp = $block['xp'] > 0 ? ' : +' . $block['xp'] . ' XP' : '';

            if ($block['xp'] > 0 && $block['xpWithBonus'] > $block['xp']) {
                $xp .= ' (+' . $block['xpWithBonus'] . ' XP avec bonus)';
            }

            $lines[] = '  - ' . $chapter->getTitre() . ' — EP #' . $episode->getNumeroSaison() . ' « ' . $episode->getTitre() . ' »' . $xp . ($dead ? ', MORT' : '');
        }

        return $lines;
    }

    private function section(string $title, array $lines): ?string
    {
        return $lines === [] ? null : '## ' . $title . "\n\n" . implode("\n", $lines);
    }

    private function field(string $label, ?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : '- **' . $label . '** : ' . $value;
    }

    private function inline(string $label, $value): ?string
    {
        return $value === null || trim((string) $value) === '' ? null : '**' . $label . '** : ' . $value;
    }

    private function paragraph(?string $text): ?string
    {
        $text = $this->text($text);

        return $text === '' ? null : $text;
    }

    private function line(?string $text): string
    {
        return preg_replace('/\s*\n+\s*/', ' ', $this->text($text));
    }

    private function text(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $text = preg_replace('#<br\s*/?>#i', "\n", $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', str_replace("\r", '', $text));
        $text = preg_replace("/ *\n */", "\n", $text);

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
