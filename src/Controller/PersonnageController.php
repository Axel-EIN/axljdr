<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Repository\PersonnageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonnageController extends AbstractController
{
    /**
     * @Route("/personnages", name="personnages")
     */
    public function viewPersonnages(PersonnageRepository $personnageRepository): Response {
        $pjs = $personnageRepository->findAllPJsSorted();
        $pnjs = $personnageRepository->findAllPNJsSorted();

        $sections = [];
        $sections[0]['name'] = "PJs";
        $sections[0]['entity'] = 'personnage';
        $sections[0]['label_one'] = "un personnage";
        $sections[0]['titleLight'] = '';
        $sections[0]['titleStrong'] = 'Personnages Joueurs';

        $sections[1]['name'] = "PNJs";
        $sections[1]['entity'] = 'personnage';
        $sections[1]['label_one'] = "un personnage";
        $sections[1]['titleLight'] = '';
        $sections[1]['titleStrong'] = 'Autres Personnages';

        $header_classname = 'characters';
        $header_up = "Les Héros de l'Aventure";
        $header_down = 'Les Personnages';
        $category = 'personnage';

        return $this->render('personnage/index.html.twig', [
            'pjs' => $pjs,
            'pnjs' => $pnjs,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category
        ]);
    }

    /**
     * @Route("/personnages/profil/{id}", name="personnage_profil")
     */
    public function viewPersonnageProfil(Personnage $personnage, PersonnageRepository $personnageRepository): Response {

        $autresPersonnages = $personnageRepository->findAllExceptOne($personnage->getId());
        shuffle($autresPersonnages);

        $xp_total = 0;
        $participations = $personnage->getParticipations();

        foreach ($participations as $une_participation) {
           $xp_total = $xp_total + $une_participation->getXpGagne();
        }

        $xp_progression = $xp_total;

        $fiche = $personnage->getFichePersonnage();
        $xp_creation = ($fiche !== null) ? $fiche->getCreationExp() : 0;
        $xp_total += $xp_creation;

        $rang = 1;

        if ($xp_total >= 360)
            $rang = 5;
        elseif ($xp_total >= 240)
            $rang = 4;
        elseif ($xp_total >= 140)
            $rang = 3;
        elseif ($xp_total >= 60)
            $rang = 2;

        return $this->render('personnage/profil.html.twig', [
            'personnage' => $personnage,
            'nom' => $personnage->getNom() . ' ' . $personnage->getPrenom(),
            'entity' => 'personnage',
            'category' => 'personnages',
            'un_element' => $personnage,
            'xp' => $xp_total,
            'xp_creation' => $xp_creation,
            'xp_progression' => $xp_progression,
            'rang' => $rang,
            'autresPersonnages' => $autresPersonnages
        ]);
    }

    /**
     * @Route("/mon_compte/fiche/{id}", name="mon_compte_fiche")
     */
    public function afficherFichePersonnage(FichePersonnage $fiche): Response
    {
        $utilisateur = $this->getUser();

        $estLeJoueur = $utilisateur !== null
            && $fiche->getPersonnage()->getJoueur() !== null
            && $fiche->getPersonnage()->getJoueur()->getId() == $utilisateur->getId();

        $xp_progression = 0;
        foreach ($fiche->getPersonnage()->getParticipations() as $participation) {
            $xp_progression += $participation->getXpGagne();
        }

        $xp_creation = $fiche->getCreationExp() ?? 0;
        $xp_total = $xp_progression + $xp_creation;

        $rang = 1;
        if ($xp_total >= 360)      $rang = 5;
        elseif ($xp_total >= 240)  $rang = 4;
        elseif ($xp_total >= 140)  $rang = 3;
        elseif ($xp_total >= 60)   $rang = 2;

        return $this->render('personnage/fiche_personnage.html.twig', [
            'fiche'          => $fiche,
            'xp_total'       => $xp_total,
            'xp_creation'    => $xp_creation,
            'xp_progression' => $xp_progression,
            'rang'           => $rang,
            'est_le_joueur'  => $estLeJoueur,
        ]);
    }
}