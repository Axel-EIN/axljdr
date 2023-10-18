<?php

namespace App\Controller;

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
    public function afficherPersonnages(PersonnageRepository $personnageRepository): Response {
        $pjs = $personnageRepository->findAllPJsSorted();
        $pnjs = $personnageRepository->findAllPNJsSorted();

        $sections = [];
        $sections[0]['name'] = "PJs";
        $sections[0]['entity'] = 'personnage';
        $sections[0]['label_one'] = "un personnage";
        $sections[0]['titleLight'] = 'Personnages';
        $sections[0]['titleStrong'] = 'Joueurs';

        $sections[1]['name'] = "PNJs";
        $sections[1]['entity'] = 'personnage';
        $sections[1]['label_one'] = "un personnage";
        $sections[1]['titleLight'] = 'Personnages';
        $sections[1]['titleStrong'] = 'Non-Joueurs';

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
    public function afficherPersonnageProfil(Personnage $personnage, PersonnageRepository $personnageRepository): Response {

        $autresPersonnages = $personnageRepository->findAllExceptOne($personnage->getId());
        shuffle($autresPersonnages);

        $xp_total = 0;
        $participations = $personnage->getParticipations();

        foreach ($participations as $une_participation) {
           $xp_total = $xp_total + $une_participation->getXpGagne();
        }

        $rang = 1;

        if ($xp_total >= 250)
            $rang = 5;
        elseif ($xp_total >= 200)
            $rang = 4;
        elseif ($xp_total >= 150)
            $rang = 3;
        elseif ($xp_total >= 100)
            $rang = 2;

        return $this->render('personnage/profil.html.twig', [
            'personnage' => $personnage,
            'nom' => $personnage->getNom() . ' ' . $personnage->getPrenom(),
            'entity' => 'personnage',
            'category' => 'personnages',
            'un_element' => $personnage,
            'xp' => $xp_total,
            'rang' => $rang,
            'autresPersonnages' => $autresPersonnages
        ]);
    }
}
