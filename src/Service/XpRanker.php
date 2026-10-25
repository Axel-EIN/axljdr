<?php
namespace App\Service;

use App\Entity\Avantage;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Entity\Episode;
use App\Repository\PersonnageRepository;

class XpRanker
{
    public const SPECIALISATION_COST = 2;

    private const TRAITS = ['constitution', 'volonte', 'reflexes', 'intuition', 'agilite', 'intelligence', 'forceStat', 'perception', 'vide'];

    private $characterRepository;

    public function __construct(PersonnageRepository $characterRepository)
    {
        $this->characterRepository = $characterRepository;
    }

    public function total(Personnage $character): int
    {
        $sheet = $character->getFichePersonnage();

        return $this->progression($character)
            + ($sheet !== null ? $this->creation($sheet) + $this->disadvantagesGain($sheet) : 0);
    }

    public function creation(FichePersonnage $sheet): int
    {
        return (int) $sheet->getCreationExp();
    }

    public function disadvantagesGain(FichePersonnage $sheet): int
    {
        return $this->advantageCost($sheet, $sheet->getDesavantage1())
            + $this->advantageCost($sheet, $sheet->getDesavantage2());
    }

    public function progression(Personnage $character): int
    {
        $total = 0;

        foreach ($character->getParticipations() as $participation) {
            $total += $participation->getXpEffectif();
        }

        return $total;
    }

    public function spent(FichePersonnage $sheet): int
    {
        return $this->traitsCost($sheet)
            + $this->skillsCost($sheet)
            + $this->advantagesCost($sheet);
    }

    public function remaining(FichePersonnage $sheet): int
    {
        return $this->total($sheet->getPersonnage()) - $this->spent($sheet);
    }

    public function sheet(FichePersonnage $sheet): array
    {
        $skills = [];

        for ($i = 1; $i <= 20; $i++) {
            $skills[$i] = [
                'rank' => $this->skillRankCost($sheet, $i),
                'specialisations' => $this->specialisationsCost($sheet, $i),
            ];
        }

        return [
            'creation' => $this->creation($sheet),
            'disadvantages' => $this->disadvantagesGain($sheet),
            'progression' => $this->progression($sheet->getPersonnage()),
            'total' => $this->total($sheet->getPersonnage()),
            'traits' => $this->traitsCost($sheet),
            'trait' => array_combine(self::TRAITS, array_map(fn (string $trait) => $this->traitCost($sheet, $trait), self::TRAITS)),
            'skills' => $this->skillsCost($sheet),
            'advantages' => $this->advantagesCost($sheet),
            'spent' => $this->spent($sheet),
            'remaining' => $this->remaining($sheet),
            'slots' => [
                'av1' => $this->advantageCost($sheet, $sheet->getAvantage1()),
                'av2' => $this->advantageCost($sheet, $sheet->getAvantage2()),
                'dv1' => $this->advantageCost($sheet, $sheet->getDesavantage1()),
                'dv2' => $this->advantageCost($sheet, $sheet->getDesavantage2()),
            ],
            'skill' => $skills,
        ];
    }

    private function traitsCost(FichePersonnage $sheet): int
    {
        return array_sum(array_map(fn (string $trait) => $this->traitCost($sheet, $trait), self::TRAITS));
    }

    private function traitCost(FichePersonnage $sheet, string $trait): int
    {
        $effective = $sheet->attributEffectif($trait);
        $free = 2 + $sheet->bonusAttribut($trait);

        if ($effective <= $free) {
            return 0;
        }

        $factor = $trait === 'vide' ? 6 : 4;

        return (int) round($factor * ($effective * ($effective + 1) / 2 - $free * ($free + 1) / 2));
    }

    private function skillsCost(FichePersonnage $sheet): int
    {
        $total = 0;

        for ($i = 1; $i <= 20; $i++) {
            $total += $this->skillRankCost($sheet, $i) + $this->specialisationsCost($sheet, $i);
        }

        return $total;
    }

    private function skillRankCost(FichePersonnage $sheet, int $i): int
    {
        if ($sheet->{'getCompetence' . $i}() === null) {
            return 0;
        }

        $value = (int) $sheet->{'getValeur' . $i}();
        $free = (int) $sheet->{'getCompEcole' . $i}();

        return max(0, (int) round($value * ($value + 1) / 2) - (int) round($free * ($free + 1) / 2));
    }

    private function specialisationsCost(FichePersonnage $sheet, int $i): int
    {
        $skill = $sheet->{'getCompetence' . $i}();

        if ($skill === null) {
            return 0;
        }

        $bought = (string) $sheet->{'getSpecialisations' . $i}();
        $offered = (string) $sheet->{'getSpeEcole' . $i}();
        $total = 0;

        for ($k = 1; $k <= 6; $k++) {
            if ($skill->{'getSpecialisation' . $k}() && ($bought[$k - 1] ?? '0') === '1' && ($offered[$k - 1] ?? '0') !== '1') {
                $total += self::SPECIALISATION_COST;
            }
        }

        return $total;
    }

    private function advantagesCost(FichePersonnage $sheet): int
    {
        return $this->advantageCost($sheet, $sheet->getAvantage1())
            + $this->advantageCost($sheet, $sheet->getAvantage2());
    }

    public function advantageCost(FichePersonnage $sheet, ?Avantage $advantage): int
    {
        if ($advantage === null) {
            return 0;
        }

        $character = $sheet->getPersonnage();
        $clan = $character->getClan() ? $character->getClan()->getId() : 0;
        $class = $character->getClasse() ? $character->getClasse()->getId() : 0;

        $discounted = $advantage->getDiscount() && (
            ($advantage->getDiscountClan() && $advantage->getDiscountClan()->getId() === $clan)
            || ($advantage->getDiscountClan2() && $advantage->getDiscountClan2()->getId() === $clan)
            || ($advantage->getDiscountClasse() && $advantage->getDiscountClasse()->getId() === $class)
            || ($advantage->getDiscountClasse2() && $advantage->getDiscountClasse2()->getId() === $class)
        );

        return (int) ($discounted ? $advantage->getDiscount() : $advantage->getCout());
    }

    public function rank(int $total): int
    {
        foreach ([5 => 360, 4 => 240, 3 => 140, 2 => 60] as $rank => $threshold) {
            if ($total >= $threshold) {
                return $rank;
            }
        }

        return 1;
    }

    public function rankEpisodeCharacters(Episode $episode)
    {
        $participations = [];
        $characterIds = [];
        foreach ($episode->getScenes() as $scene) {
            foreach ($scene->getParticipations() as $participation) {
                if ($participation->getEstPj()) {
                    $participations[] = $participation;
                    $characterIds[] = $participation->getPersonnage()->getId();
                }
            }
        }
        if ($participations)
            return $this->rankCharacters($characterIds, $participations);
        else
            return false;
    }

    private function rankCharacters($characterIds, $participations)
    {
        $ranking = [];
        foreach (array_unique($characterIds) as $characterId) {
            $characterParticipations = array_filter($participations, fn ($participation) => $participation->getPersonnage()->getId() == $characterId);
            $ranking[] = $this->sumParticipations($characterId, $characterParticipations);
        }

        usort($ranking, fn ($a, $b) => $b['xp'] <=> $a['xp']);

        return $ranking;
    }

    public function characterEpisodeTotal(Episode $episode, $characterId)
    {
        $participations = [];
        foreach ($episode->getScenes() as $scene) {
            foreach ($scene->getParticipations() as $participation) {
                if ($participation->getEstPj()) {
                    if ($participation->getPersonnage()->getId() == $characterId)
                        $participations[] = $participation;
                }
            }
        }
        if ($participations)
            return $this->sumParticipations($characterId, $participations);
        else
            return false;
    }

    public function totalsByEpisode(array $participations, array $characterIds): array
    {
        $groups = [];
        foreach ($participations as $participation) {
            $groups[$participation->getPersonnage()->getId()][$participation->getScene()->getEpisodeParent()->getId()][] = $participation;
        }

        $totals = [];
        foreach ($characterIds as $characterId) {
            foreach ($groups[$characterId] ?? [] as $episodeId => $episodeParticipations) {
                $totals[$characterId][$episodeId] = $this->sumParticipations($characterId, $episodeParticipations);
            }
        }

        return $totals;
    }

    private function sumParticipations($characterId, $participations)
    {
        $character = $this->characterRepository->find($characterId);
        $totalXp = 0;
        $totalXpWithBonus = 0;
        $dead = 0;
        foreach ($participations as $participation) {
            $totalXp += $participation->getXpGagne();
            $totalXpWithBonus += $participation->getXpEffectif();
            if ($participation->getEstMort() == true)
                $dead = 1;
        }

        return [
            'id' => $character->getId(),
            'firstName' => $character->getPrenom(),
            'icon' => $character->getIcone(),
            'player' => $character->getJoueur(),
            'xp' => $totalXp,
            'xpWithBonus' => $totalXpWithBonus,
            'dead' => $dead,
        ];
    }
}
