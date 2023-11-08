<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Entity\Classe;
use App\Entity\Ecole;
use App\Repository\AvantageRepository;
use App\Repository\EcoleRepository;
use App\Repository\ClasseRepository;
use App\Repository\RuleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ReglesController extends AbstractController
{
    /**
     * @Route("/regles", name="regles")
     */
    public function viewRegles(RuleRepository $ruleRepository,  ClasseRepository $classeRepository): Response
    {
        $rulesSimplesBases = $ruleRepository->findAllReglesSimpleBasesOrdered();
        $classes = $classeRepository->findAll();
        $rulesBibliothequesBases = $ruleRepository->findAllBibliothequesBasesOrdered();
        $rulesAnnexes = $ruleRepository->findAllAnnexesOrdered();

        $sections = [];

        // Section 1 : Règles de Bases
        $sections[0]['name'] = "Règles de Bases";
        $sections[0]['entity'] = 'rule';
        $sections[0]['label_one'] = 'une Règle';
        $sections[0]['titleLight'] = 'Les ';
        $sections[0]['titleStrong'] = 'Bases';

        
        // Section 2 : Les Classes
        $sections[1]['name'] = "Classes";
        $sections[1]['entity'] = 'classe';
        $sections[1]['label_one'] = 'une Classe';
        $sections[1]['titleLight'] = 'Les ';
        $sections[1]['titleStrong'] = 'Classes';

        
        // Section 3 : Les Bibliothèques de Bases (Liste d'Objet de Règles comme les Avantages/Désavantages/Compétences/Equipement/Sorts/Kiho/Tatoo)
        $sections[2]['name'] = "Bibliothèques";
        $sections[2]['entity'] = 'rule';
        $sections[2]['label_one'] = 'une Bibliothèque';
        $sections[2]['titleLight'] = 'Les ';
        $sections[2]['titleStrong'] = 'Bibliothèques';

        // Section 4 : Les Règles Annexes (Règles Simples Annexes ou Bibliothèques Annexes)
        $sections[3]['name'] = "Règles Annexes";
        $sections[3]['entity'] = 'rule';
        $sections[3]['label_one'] = 'une Règle';
        $sections[3]['titleLight'] = 'Les ';
        $sections[3]['titleStrong'] = 'Annexes';

        $header_classname = 'rules';
        $header_up = "Mécanique de Jeu";
        $header_down = "Règles";
        $category = 'regles';

        return $this->render('regles/index.html.twig', [
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category,
            'sections' => $sections,
            'rulesSimplesBases' => $rulesSimplesBases,
            'classes' => $classes,
            'rulesBibliothequesBases' => $rulesBibliothequesBases,
            'rulesAnnexes' => $rulesAnnexes,
        ]);
    }

    /**
     * @Route("/regles/rule/{id}", name="regles_rule")
     */
    public function viewRule(Rule $rule, RuleRepository $ruleRepository, Request $request, AvantageRepository $avantageRepository): Response
    {
        $autresRules = $ruleRepository->findAllSameTypeExceptOneSorted($rule->getId(), $rule->getBase(), $rule->getListEntity());

        if (!empty($rule->getListEntity())) {

            $items = ${$rule->getListEntity() . 'Repository'}->findAll();

            $itemsTabs = [];
            $itemsFilters = [];
            foreach ($items as $item) {
                $itemsTabs[] = $item->{ 'get' .  ucfirst( $rule->getListTabField() ) }();
                $itemsFilters[] = $item->{ 'get' . ucfirst( $rule->getListFilterField() ) }();
            }
            $tabs = array_unique($itemsTabs);
            sort($tabs);
            $filters = array_unique($itemsFilters);
            sort($filters);

            if (!empty($request->query->get('tab')))
                $items = ${$rule->getListEntity() . 'Repository'}->{'findAll' . ucfirst( $rule->getListTabField() ) }($request->query->get('tab'));

            if (!empty($request->query->get('filter')))
            {
                $filter = $request->query->get('filter');
                $items = array_filter($items, function ($obj) use ($filter) {
                    return $obj->getType() == $filter;
                  });
            }

            return $this->render('regles/library.html.twig', [
                'library' => $rule,
                'nom' => $rule->getNom(),
                'un_element' => $rule,
                'entity' => 'rule',
                'category' => 'regles',
                'autresRules' => $autresRules,
                'items' => $items,
                'tabs' => $tabs,
                'filters' => $filters
            ]);
        }

        return $this->render('regles/rule.html.twig', [
            'rule' => $rule,
            'nom' => $rule->getNom(),
            'entity' => 'rule',
            'category' => 'regles',
            'un_element' => $rule,
            'autresRules' => $autresRules
        ]);
    }

    /**
     * @Route("/regles/classe/{id}", name="regles_classe")
     */
    public function viewClasse(Classe $classe, EcoleRepository $ecoleRepository, ClasseRepository $classeRepository): Response
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
    public function viewEcole(Ecole $ecole, EcoleRepository $ecoleRepository): Response
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