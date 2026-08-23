<?php
namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;

class ClasseurHistorique
{
    public function pourLieu(Lieu $lieu): array
    {
        $scenes = $lieu->getScenes()->toArray();
        usort($scenes, [$this, 'comparer']);

        $lignes = [];
        foreach ($scenes as $scene) {
            $lignes[] = ['scene' => $scene, 'xp' => 0, 'bonus' => false, 'mort' => false];
        }

        return $this->grouperParEpisode($lignes);
    }

    public function pourPersonnage(Personnage $personnage): array
    {
        $participations = $personnage->getParticipations()->toArray();
        $classeur = $this;
        usort($participations, function ($a, $b) use ($classeur) {
            return $classeur->comparer($a->getScene(), $b->getScene());
        });

        $lignes = [];
        foreach ($participations as $participation) {
            $lignes[] = [
                'scene' => $participation->getScene(),
                'xp' => $participation->getXpEffectif(),
                'bonus' => $participation->getXpBonus(),
                'mort' => $participation->getEstMort(),
            ];
        }

        return $this->grouperParEpisode($lignes);
    }

    public function comparer(Scene $a, Scene $b): int
    {
        return $this->rang($b) <=> $this->rang($a);
    }

    private function rang(Scene $scene): array
    {
        $episode = $scene->getEpisodeParent();
        $chapitre = $episode->getChapitreParent();

        return [
            $chapitre->getSaisonParent()->getNumero(),
            $chapitre->getNumero(),
            $episode->getNumero(),
            $scene->getNumero(),
        ];
    }

    /** @param array $lignes déjà triées */
    private function grouperParEpisode(array $lignes): array
    {
        $historique = [];
        foreach ($lignes as $ligne) {
            $episode = $ligne['scene']->getEpisodeParent();
            $id = $episode->getId();
            $historique[$id]['episode'] = $episode;
            $historique[$id]['xp'] = ($historique[$id]['xp'] ?? 0) + $ligne['xp'];
            $historique[$id]['lignes'][] = $ligne;
        }

        return array_values($historique);
    }
}
