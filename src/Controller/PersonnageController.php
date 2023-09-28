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
        $sections[0]['titleLight'] = 'Personnages';
        $sections[0]['titleStrong'] = 'Joueurs';
        $sections[0]['element'] = 'personnage';

        $sections[1]['name'] = "PNJs";
        $sections[1]['titleLight'] = 'Personnages';
        $sections[1]['titleStrong'] = 'Non-Joueurs';
        $sections[1]['element'] = 'personnage';

        $header_classname = 'characters';
        $header_up = "Les Héros de l'Aventure";
        $header_down = 'Les Personnages';

        return $this->render('personnage/index.html.twig', [
            'pjs' => $pjs,
            'pnjs' => $pnjs,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
        ]);
    }

    /**
     * @Route("/personnages/profil/{id}", name="personnage_profil")
     */
    public function afficherPersonnageProfil(Personnage $personnage): Response {
        return $this->render('personnage/profil.html.twig', [
            'personnage' => $personnage,
        ]);
    }
}
