<?php
namespace App\Service;

use App\Entity\Avantage;
use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Entity\Scene;
use App\Entity\Episode;
use App\Entity\Chapitre;
use App\Entity\Saison;
use App\Repository\PersonnageRepository;

class ClasseurXP
{
    private const SPECIALISATION_COST = 2;

    private $persoRepo;

    public function __construct(PersonnageRepository $personnageRepository)
    {
        $this->persoRepo = $personnageRepository;
    }

    public function total(Personnage $personnage): int
    {
        $total = 0;

        foreach ($personnage->getParticipations() as $participation) {
            $total += $participation->getXpEffectif();
        }

        $fiche = $personnage->getFichePersonnage();

        return $total + ($fiche !== null ? (int) $fiche->getCreationExp() : 0);
    }

    public function earned(FichePersonnage $fiche): int
    {
        return $this->total($fiche->getPersonnage())
            + $this->advantageCost($fiche, $fiche->getDesavantage1())
            + $this->advantageCost($fiche, $fiche->getDesavantage2());
    }

    public function spent(FichePersonnage $fiche): int
    {
        return $this->traitsCost($fiche)
            + $this->skillsCost($fiche)
            + $this->advantageCost($fiche, $fiche->getAvantage1())
            + $this->advantageCost($fiche, $fiche->getAvantage2());
    }

    public function remaining(FichePersonnage $fiche): int
    {
        return $this->earned($fiche) - $this->spent($fiche);
    }

    private function traitsCost(FichePersonnage $fiche): int
    {
        $personnage = $fiche->getPersonnage();
        $bonuses = array_filter([
            $personnage->getFamille() ? $personnage->getFamille()->getBonusStatNom() : null,
            $personnage->getEcole() ? $personnage->getEcole()->getBonusStatNom() : null,
        ]);

        $total = 0;

        foreach (['constitution', 'volonte', 'reflexes', 'intuition', 'agilite', 'intelligence', 'forceStat', 'perception', 'vide'] as $trait) {
            $bonus = count(array_keys($bonuses, $trait, true));
            $effective = (int) $fiche->{'get' . ucfirst($trait)}() + $bonus;
            $free = 2 + $bonus;

            if ($effective > $free) {
                $factor = $trait === 'vide' ? 6 : 4;
                $total += (int) round($factor * ($effective * ($effective + 1) / 2 - $free * ($free + 1) / 2));
            }
        }

        return $total;
    }

    private function skillsCost(FichePersonnage $fiche): int
    {
        $total = 0;

        for ($i = 1; $i <= 20; $i++) {
            $competence = $fiche->{'getCompetence' . $i}();

            if ($competence === null) {
                continue;
            }

            $value = (int) $fiche->{'getValeur' . $i}();
            $free = (int) $fiche->{'getCompEcole' . $i}();
            $total += max(0, (int) round($value * ($value + 1) / 2) - (int) round($free * ($free + 1) / 2));

            $bought = (string) $fiche->{'getSpecialisations' . $i}();
            $offered = (string) $fiche->{'getSpeEcole' . $i}();

            for ($k = 1; $k <= 6; $k++) {
                if ($competence->{'getSpecialisation' . $k}() && ($bought[$k - 1] ?? '0') === '1' && ($offered[$k - 1] ?? '0') !== '1') {
                    $total += self::SPECIALISATION_COST;
                }
            }
        }

        return $total;
    }

    public function advantageCost(FichePersonnage $fiche, ?Avantage $avantage): int
    {
        if ($avantage === null) {
            return 0;
        }

        $personnage = $fiche->getPersonnage();
        $clan = $personnage->getClan() ? $personnage->getClan()->getId() : 0;
        $classe = $personnage->getClasse() ? $personnage->getClasse()->getId() : 0;

        $discounted = $avantage->getDiscount() && (
            ($avantage->getDiscountClan() && $avantage->getDiscountClan()->getId() === $clan)
            || ($avantage->getDiscountClan2() && $avantage->getDiscountClan2()->getId() === $clan)
            || ($avantage->getDiscountClasse() && $avantage->getDiscountClasse()->getId() === $classe)
        );

        return (int) ($discounted ? $avantage->getDiscount() : $avantage->getCout());
    }

    public function rank(int $total): int
    {
        foreach ([5 => 360, 4 => 240, 3 => 140, 2 => 60] as $rang => $seuil) {
            if ($total >= $seuil) {
                return $rang;
            }
        }

        return 1;
    }

    public function classerPersosAventure(Saison $saisons)
    {
        $participations = [];
        $personnages_id = [];
        foreach($saisons as $une_saison) {
            foreach($une_saison->getChapitres() as $un_chapitre) {
                foreach($un_chapitre->getEpisodes() as $un_episode) {
                    foreach($un_episode->getScenes() as $une_scene) {
                        foreach($une_scene->getParticipations() as $une_participation) {
                            if ($une_participation->getEstPj()) {
                                $participations[] = $une_participation;
                                $personnages_id[] = $une_participation->getPersonnage()->getId();
                            }
                        }
                    }
                }
            }
        }
        if ($participations)
            return $this->classer($personnages_id, $participations);
        else
            return false;
    }

    public function classerPersosSaison(Saison $saison)
    {
        $participations = [];
        $personnages_id = [];
        foreach($saison->getChapitres() as $un_chapitre) {
            foreach($un_chapitre->getEpisodes() as $un_episode) {
                foreach($un_episode->getScenes() as $une_scene) {
                    foreach($une_scene->getParticipations() as $une_participation) {
                        if ($une_participation->getEstPj()) {
                            $participations[] = $une_participation;
                            $personnages_id[] = $une_participation->getPersonnage()->getId();
                        }
                    }
                }
            }
        }
        if ($participations)
            return $this->classer($personnages_id, $participations);
        else
            return false;
    }

    public function classerPersosChapitre(Chapitre $chapitre)
    {
        $participations = [];
        $personnages_id = [];
        foreach($chapitre->getEpisodes() as $un_episode) {
            foreach($un_episode->getScenes() as $une_scene) {
                foreach($une_scene->getParticipations() as $une_participation) {
                    if ($une_participation->getEstPj()) {
                        $participations[] = $une_participation;
                        $personnages_id[] = $une_participation->getPersonnage()->getId();
                    }
                }
            }
        }
        if ($participations)
            return $this->classer($personnages_id, $participations);
        else
            return false;
    }

    public function classerPersosEpisode(Episode $episode)
    {
        $participations = [];
        $personnages_id = [];
        foreach($episode->getScenes() as $une_scene) {
            foreach($une_scene->getParticipations() as $une_participation) {
                if ($une_participation->getEstPj()) {
                    $participations[] = $une_participation;
                    $personnages_id[] = $une_participation->getPersonnage()->getId();
                    $un_perso =  $une_participation->getPersonnage();
                }
            }
        }
        if ($participations)
            return $this->classer($personnages_id, $participations);
        else
            return false;
    }

    public function classerPersosScene(Scene $scene)
    {
        $participations = [];
        $personnages_id = [];
        foreach($scene->getParticipations() as $une_participation) {
            if ($une_participation->getEstPj()) {
                $participations[] = $une_participation;
                $personnages_id[] = $une_participation->getPersonnage()->getId();
            }
        }
        if ($participations)
            return $this->classer($personnages_id, $participations);
        else
            return false;
    }

    public function classer($personnages_id, $participations)
    {
        $classement = [];
        $i = 0;
        foreach(array_unique($personnages_id) as $un_personnage_id) {
            $personnage = $this->persoRepo->find($un_personnage_id);
            $totalXp = 0;
            $totalXpAvecBonus = 0;
            $estMort = 0;

            foreach($participations as $une_participation) {
                if ($une_participation->getPersonnage()->getId() == $un_personnage_id) {
                    $totalXp          += $une_participation->getXpGagne();
                    $totalXpAvecBonus += $une_participation->getXpEffectif();
                    if ($une_participation->getEstMort() == true)
                        $estMort = 1;
                }
            }

            $classement[$i]['id'] = $personnage->getId();
            $classement[$i]['prenom'] = $personnage->getPrenom();
            $classement[$i]['icone'] = $personnage->getIcone();
            $classement[$i]['joueur'] = $personnage->getJoueur();
            $classement[$i]['xp'] = $totalXp;
            $classement[$i]['xpWithBonus'] = $totalXpAvecBonus;
            $classement[$i]['estMort'] = $estMort;
            $i++;
        }

        usort($classement, function ($a, $b) {
            return strcmp($a['xp'], $b['xp']);
        } );

        return array_reverse($classement);
    }

    public function cumulUnPersosAventure(Saison $saisons, $persoId)
    {
        $participations = [];
        foreach($saisons as $une_saison) {
            foreach($une_saison->getChapitres() as $un_chapitre) {
                foreach($un_chapitre->getEpisodes() as $un_episode) {
                    foreach($un_episode->getScenes() as $une_scene) {
                        foreach($une_scene->getParticipations() as $une_participation) {
                            if ($une_participation->getEstPj()) {
                                if ($une_participation->getPersonnage()->getId() == $persoId)
                                    $participations[] = $une_participation;
                            }
                        }
                    }
                }
            }
        }
        if ($participations)
            return $this->cumuler($persoId, $participations);
        else
            return false;
    }

    public function cumulUnPersoSaison(Saison $saison, $persoId)
    {
        $participations = [];
        foreach($saison->getChapitres() as $un_chapitre) {
            foreach($un_chapitre->getEpisodes() as $un_episode) {
                foreach($un_episode->getScenes() as $une_scene) {
                    foreach($une_scene->getParticipations() as $une_participation) {
                        if ($une_participation->getEstPj()) {
                            if ($une_participation->getPersonnage()->getId() == $persoId)
                                $participations[] = $une_participation;
                        }
                    }
                }
            }
        }
        if ($participations)
            return $this->cumuler($persoId, $participations);
        else
            return false;
    }

    public function cumulUnPersoChapitre(Chapitre $chapitre, $persoId)
    {
        $participations = [];
        foreach($chapitre->getEpisodes() as $un_episode) {
            foreach($un_episode->getScenes() as $une_scene) {
                foreach($une_scene->getParticipations() as $une_participation) {
                    if ($une_participation->getEstPj()) {
                        if ($une_participation->getPersonnage()->getId() == $persoId)
                            $participations[] = $une_participation;
                    }
                }
            }
        }
        if ($participations)
            return $this->cumuler($persoId, $participations);
        else
            return false;
    }

    public function cumulUnPersoEpisode(Episode $episode, $persoId)
    {
        $participations = [];
        foreach($episode->getScenes() as $une_scene) {
            foreach($une_scene->getParticipations() as $une_participation) {
                if ($une_participation->getEstPj()) {
                    if ($une_participation->getPersonnage()->getId() == $persoId)
                        $participations[] = $une_participation;
                }
            }
        }
        if ($participations)
            return $this->cumuler($persoId, $participations);
        else
            return false;
    }

    public function cumulUnPersoScene(Scene $scene, $persoId)
    {
        $participations = [];
        foreach($scene->getParticipations() as $une_participation) {
            if ($une_participation->getEstPj()) {
                if ($une_participation->getPersonnage()->getId() == $persoId)
                    $participations[] = $une_participation;
            }
        }
        if ($participations)
            return $this->cumuler($persoId, $participations);
        else
            return false;
    }

    public function cumuler($persoId, $participations)
    {
        $personnage = $this->persoRepo->find($persoId);
        $totalXp = 0;
        $estMort = 0;
        foreach($participations as $une_participation) {
            $totalXp += $une_participation->getXpGagne();
            if ($une_participation->getEstMort() == true)
                $estMort = 1;
        }
        $cumul = [];
        $cumul['id'] = $personnage->getId();
        $cumul['prenom'] = $personnage->getPrenom();
        $cumul['icone'] = $personnage->getIcone();
        $cumul['joueur'] = $personnage->getJoueur();
        $cumul['xp'] = $totalXp;
        $cumul['estMort'] = $estMort;
        return $cumul;
    }
}