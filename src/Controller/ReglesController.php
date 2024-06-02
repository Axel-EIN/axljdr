<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Entity\Classe;
use App\Entity\Ecole;
use App\Repository\EcoleRepository;
use App\Repository\ClasseRepository;
use App\Repository\RuleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReglesController extends AbstractController
{
    /**
     * @Route("/regles", name="regles")
     */
    public function afficherRegles(RuleRepository $ruleRepository,  ClasseRepository $classeRepository): Response
    {
        $rules = $ruleRepository->findAllTypeSorted(true);
        $rulesDivers = $ruleRepository->findAllTypeSorted(false);
        $classes = $classeRepository->findAll();

        $sections = [];

        $sections[0]['name'] = "Règles de Bases";
        $sections[0]['entity'] = 'rule';
        $sections[0]['label_one'] = 'une règle';
        $sections[0]['titleLight'] = 'Règles de';
        $sections[0]['titleStrong'] = 'Bases';

        $sections[1]['name'] = "Classes";
        $sections[1]['entity'] = 'classe';
        $sections[1]['label_one'] = 'une classe';
        $sections[1]['titleLight'] = 'Les';
        $sections[1]['titleStrong'] = 'Classes';

        $sections[2]['name'] = "Règles Annexes";
        $sections[2]['entity'] = 'rule';
        $sections[2]['label_one'] = 'une règle';
        $sections[2]['titleLight'] = 'Règles';
        $sections[2]['titleStrong'] = 'Annexes';

        $header_classname = 'rules';
        $header_up = "Mécanique de Jeu";
        $header_down = "Règles";
        $category = 'regles';

        return $this->render('regles/index.html.twig', [
            'rules' => $rules,
            'rulesDivers' => $rulesDivers,
            'classes' => $classes,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category,
        ]);
    }

    /**
     * @Route("/regles/rule/{id}", name="regles_rule")
     */
    public function afficherRule(Rule $rule): Response
    {
        return $this->render('regles/rule.html.twig', [
            'rule' => $rule,
            'nom' => $rule->getNom(),
            'entity' => 'rule',
            'category' => 'regles',
            'un_element' => $rule,
        ]);
    }

    /**
     * @Route("/regles/classe/{id}", name="regles_classe")
     */
    public function afficherClasse(Classe $classe, EcoleRepository $ecoleRepository, ClasseRepository $classeRepository): Response
    {
        $autresClasses = $classeRepository->findAllExceptOne($classe->getId());
        $classes = $classeRepository->FindAll();
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
            'autresClasses' => $autresClasses,
            'classes' => $classes
        ]);
    }

    /**
     * @Route("/regles/ecole/{id}", name="regles_ecole")
     */
    public function afficherEcole(Ecole $ecole, EcoleRepository $ecoleRepository): Response
    {
        $autresEcoles = $ecoleRepository->findAllByClanExceptOne($ecole->getClan()->getId(), $ecole->getId());

        return $this->render('regles/ecole.html.twig', [
            'ecole' => $ecole,
            'nom' => $ecole->getNom(),
            'entity' => 'ecole',
            'category' => 'regles',
            'un_element' => $ecole,
            'autresEcoles' => $autresEcoles,
        ]);
    }
}
