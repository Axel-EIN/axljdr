<?php
namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;

class HistoryRanker
{
    public function forLocation(Lieu $location): array
    {
        $scenes = $location->getScenes()->toArray();
        usort($scenes, [$this, 'compare']);

        $lines = [];
        foreach ($scenes as $scene) {
            $lines[] = ['scene' => $scene, 'xp' => 0, 'xpWithBonus' => 0, 'dead' => false];
        }

        return $this->groupByEpisode($lines);
    }

    public function forCharacter(Personnage $character): array
    {
        $participations = $character->getParticipations()->toArray();
        $ranker = $this;
        usort($participations, function ($a, $b) use ($ranker) {
            return $ranker->compare($a->getScene(), $b->getScene());
        });

        $lines = [];
        foreach ($participations as $participation) {
            $lines[] = [
                'scene' => $participation->getScene(),
                'xp' => (int) $participation->getXpGagne(),
                'xpWithBonus' => $participation->getXpEffectif(),
                'dead' => $participation->getEstMort(),
            ];
        }

        return $this->groupByEpisode($lines);
    }

    public function compare(Scene $a, Scene $b): int
    {
        return $this->rank($b) <=> $this->rank($a);
    }

    private function rank(Scene $scene): array
    {
        $episode = $scene->getEpisodeParent();
        $chapter = $episode->getChapitreParent();

        return [
            $chapter->getSaisonParent()->getNumero(),
            $chapter->getNumero(),
            $episode->getNumero(),
            $scene->getNumero(),
        ];
    }

    private function groupByEpisode(array $lines): array
    {
        $history = [];
        foreach ($lines as $line) {
            $episode = $line['scene']->getEpisodeParent();
            $id = $episode->getId();
            $history[$id]['episode'] = $episode;
            $history[$id]['xp'] = ($history[$id]['xp'] ?? 0) + $line['xp'];
            $history[$id]['xpWithBonus'] = ($history[$id]['xpWithBonus'] ?? 0) + $line['xpWithBonus'];
            $history[$id]['lines'][] = $line;
        }

        return array_values($history);
    }
}
