<?php
namespace App\Service;

use App\Entity\Scene;
use App\Entity\Episode;
use App\Entity\Chapitre;
use App\Entity\Saison;
use App\Repository\PersonnageRepository;

class ClasseurXP
{
    private $persoRepo;

    public function __construct(PersonnageRepository $personnageRepository)
    {
        $this->persoRepo = $personnageRepository;
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
            $estMort = 0;

            // Pour chaque participations cumul de l'XP pour chaque personnage différent
            foreach($participations as $une_participation) {
                if ($une_participation->getPersonnage()->getId() == $un_personnage_id) {
                    $totalXp += $une_participation->getXpGagne();
                    if ($une_participation->getEstMort() == true)
                        $estMort = 1; 
                }
            }
            
            // Création d'une entrée dans le le tableau de classement
            $classement[$i]['id'] = $personnage->getId();
            $classement[$i]['prenom'] = $personnage->getPrenom();
            $classement[$i]['icone'] = $personnage->getIcone();
            $classement[$i]['joueur'] = $personnage->getJoueur();
            $classement[$i]['xp'] = $totalXp;
            $classement[$i]['estMort'] = $estMort;
            $i++;
        }

        // Classement selon le champ xp
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