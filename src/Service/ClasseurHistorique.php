<?php
namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;

/**
 * Historique affiché sur les pages Lieu et Profil : les scènes groupées par
 * épisode, du plus récent au plus ancien. Faute d'horodatage en base, « récent »
 * se lit sur les numéros donnés par le MJ, du plus élevé au plus bas, à chacun
 * des quatre niveaux : saison, chapitre, épisode, scène.
 *
 * Forme rendue : [ ['episode' => Episode, 'xp' => int, 'lignes' => [
 *   ['scene' => Scene, 'xp' => int, 'bonus' => bool, 'mort' => bool], … ]], … ]
 */
class ClasseurHistorique
{
    /** Scènes où un lieu apparaît. */
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

    /** Participations d'un personnage, avec l'XP gagné et la mort éventuelle. */
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

    /** Ordre décroissant : le plus récent d'abord. */
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
