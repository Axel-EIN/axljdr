<?php

namespace App\Controller;

use App\Entity\Ecole;
use App\Entity\Classe;
use App\Repository\EcoleRepository;
use App\Repository\ClasseRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReglesController extends AbstractController
{
    /**
     * @Route("/regles", name="regles")
     */
    public function afficherRegles(EcoleRepository $ecoleRepository, ClasseRepository $classeRepository): Response
    {
        $classes = $classeRepository->findAll();
        $ecoles = $ecoleRepository->findAllSorted();

        $sections = [];
        $sections[0]['name'] = "Classes";
        $sections[0]['entity'] = 'classe';
        $sections[0]['label_one'] = 'une classe';
        $sections[0]['titleLight'] = 'Les';
        $sections[0]['titleStrong'] = 'Classes';

        $sections[1]['name'] = "Écoles";
        $sections[1]['entity'] = 'ecole';
        $sections[1]['label_one'] = 'une école';
        $sections[1]['titleLight'] = 'Les';
        $sections[1]['titleStrong'] = 'Écoles';

        $header_classname = 'rules';
        $header_up = "Mécanique de Jeu";
        $header_down = "Règles";
        $category = 'regles';

        return $this->render('regles/index.html.twig', [
            'ecoles' => $ecoles,
            'classes' => $classes,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category,
        ]);
    }

    /**
     * @Route("/regles/classe/{id}", name="regles_classe")
     */
    public function afficherClasse(Classe $classe, EcoleRepository $ecoleRepository, ClasseRepository $classeRepository): Response
    {
        $autresClasses = $classeRepository->findAllExceptOne($classe->getId());
        $personnagesClasse = $classe->getPersonnages()->toArray();
        $ecolesClasse = $ecoleRepository->findByClasseSorted($classe->getId());
        shuffle($personnagesClasse);

        return $this->render('regles/classe.html.twig', [
            'classe' => $classe,
            'nom' => $classe->getNom(),
            'entity' => 'classe',
            'category' => 'regles',
            'un_element' => $classe,
            'personnagesClasse' => $personnagesClasse,
            'ecolesClasse' => $ecolesClasse,
            'autresClasses' => $autresClasses
        ]);
    }

    /**
     * @Route("/regles/ecole/{id}", name="regles_ecole")
     */
    public function afficherEcole(Ecole $ecole): Response
    {
        return $this->render('regles/ecole.html.twig', [
            'ecole' => $ecole,
            'nom' => $ecole->getNom(),
            'entity' => 'ecole',
            'category' => 'regles',
            'un_element' => $ecole,
        ]);
    }
}
