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
    public const SPECIALISATION_COST = 2;

    private const TRAITS = ['constitution', 'volonte', 'reflexes', 'intuition', 'agilite', 'intelligence', 'forceStat', 'perception', 'vide'];

    private $persoRepo;

    public function __construct(PersonnageRepository $personnageRepository)
    {
        $this->persoRepo = $personnageRepository;
    }

    public function total(Personnage $personnage): int
    {
        $fiche = $personnage->getFichePersonnage();

        return $this->progression($personnage)
            + ($fiche !== null ? $this->creation($fiche) + $this->disadvantagesGain($fiche) : 0);
    }

    public function creation(FichePersonnage $fiche): int
    {
        return (int) $fiche->getCreationExp();
    }

    public function disadvantagesGain(FichePersonnage $fiche): int
    {
        return $this->advantageCost($fiche, $fiche->getDesavantage1())
            + $this->advantageCost($fiche, $fiche->getDesavantage2());
    }

    public function progression(Personnage $personnage): int
    {
        $total = 0;

        foreach ($personnage->getParticipations() as $participation) {
            $total += $participation->getXpEffectif();
        }

        return $total;
    }

    public function spent(FichePersonnage $fiche): int
    {
        return $this->traitsCost($fiche)
            + $this->skillsCost($fiche)
            + $this->advantagesCost($fiche);
    }

    public function remaining(FichePersonnage $fiche): int
    {
        return $this->total($fiche->getPersonnage()) - $this->spent($fiche);
    }

    public function sheet(FichePersonnage $fiche): array
    {
        $skills = [];

        for ($i = 1; $i <= 20; $i++) {
            $skills[$i] = [
                'rang' => $this->skillRankCost($fiche, $i),
                'specialisations' => $this->specialisationsCost($fiche, $i),
            ];
        }

        return [
            'creation' => $this->creation($fiche),
            'desavantages' => $this->disadvantagesGain($fiche),
            'progression' => $this->progression($fiche->getPersonnage()),
            'total' => $this->total($fiche->getPersonnage()),
            'traits' => $this->traitsCost($fiche),
            'trait' => array_combine(self::TRAITS, array_map(fn (string $trait) => $this->traitCost($fiche, $trait), self::TRAITS)),
            'competences' => $this->skillsCost($fiche),
            'avantages' => $this->advantagesCost($fiche),
            'depense' => $this->spent($fiche),
            'restant' => $this->remaining($fiche),
            'slots' => [
                'av1' => $this->advantageCost($fiche, $fiche->getAvantage1()),
                'av2' => $this->advantageCost($fiche, $fiche->getAvantage2()),
                'dv1' => $this->advantageCost($fiche, $fiche->getDesavantage1()),
                'dv2' => $this->advantageCost($fiche, $fiche->getDesavantage2()),
            ],
            'competence' => $skills,
        ];
    }

    private function traitsCost(FichePersonnage $fiche): int
    {
        return array_sum(array_map(fn (string $trait) => $this->traitCost($fiche, $trait), self::TRAITS));
    }

    private function traitCost(FichePersonnage $fiche, string $trait): int
    {
        $personnage = $fiche->getPersonnage();
        $bonuses = array_filter([
            $personnage->getFamille() ? $personnage->getFamille()->getBonusStatNom() : null,
            $personnage->getEcole() ? $personnage->getEcole()->getBonusStatNom() : null,
        ]);

        $bonus = count(array_keys($bonuses, $trait, true));
        $effective = (int) $fiche->{'get' . ucfirst($trait)}() + $bonus;
        $free = 2 + $bonus;

        if ($effective <= $free) {
            return 0;
        }

        $factor = $trait === 'vide' ? 6 : 4;

        return (int) round($factor * ($effective * ($effective + 1) / 2 - $free * ($free + 1) / 2));
    }

    private function skillsCost(FichePersonnage $fiche): int
    {
        $total = 0;

        for ($i = 1; $i <= 20; $i++) {
            $total += $this->skillRankCost($fiche, $i) + $this->specialisationsCost($fiche, $i);
        }

        return $total;
    }

    private function skillRankCost(FichePersonnage $fiche, int $i): int
    {
        if ($fiche->{'getCompetence' . $i}() === null) {
            return 0;
        }

        $value = (int) $fiche->{'getValeur' . $i}();
        $free = (int) $fiche->{'getCompEcole' . $i}();

        return max(0, (int) round($value * ($value + 1) / 2) - (int) round($free * ($free + 1) / 2));
    }

    private function specialisationsCost(FichePersonnage $fiche, int $i): int
    {
        $competence = $fiche->{'getCompetence' . $i}();

        if ($competence === null) {
            return 0;
        }

        $bought = (string) $fiche->{'getSpecialisations' . $i}();
        $offered = (string) $fiche->{'getSpeEcole' . $i}();
        $total = 0;

        for ($k = 1; $k <= 6; $k++) {
            if ($competence->{'getSpecialisation' . $k}() && ($bought[$k - 1] ?? '0') === '1' && ($offered[$k - 1] ?? '0') !== '1') {
                $total += self::SPECIALISATION_COST;
            }
        }

        return $total;
    }

    private function advantagesCost(FichePersonnage $fiche): int
    {
        return $this->advantageCost($fiche, $fiche->getAvantage1())
            + $this->advantageCost($fiche, $fiche->getAvantage2());
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
            || ($avantage->getDiscountClasse2() && $avantage->getDiscountClasse2()->getId() === $classe)
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

        usort($classement, fn ($a, $b) => $b['xp'] <=> $a['xp']);

        return $classement;
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
        $totalXpAvecBonus = 0;
        $estMort = 0;
        foreach($participations as $une_participation) {
            $totalXp += $une_participation->getXpGagne();
            $totalXpAvecBonus += $une_participation->getXpEffectif();
            if ($une_participation->getEstMort() == true)
                $estMort = 1;
        }
        $cumul = [];
        $cumul['id'] = $personnage->getId();
        $cumul['prenom'] = $personnage->getPrenom();
        $cumul['icone'] = $personnage->getIcone();
        $cumul['joueur'] = $personnage->getJoueur();
        $cumul['xp'] = $totalXp;
        $cumul['xpWithBonus'] = $totalXpAvecBonus;
        $cumul['estMort'] = $estMort;
        return $cumul;
    }
}