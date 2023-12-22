<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Entity\Ecole;
use App\Entity\Classe;
use App\Entity\Library;
use App\Repository\RuleRepository;
use App\Repository\SortRepository;
use App\Repository\EcoleRepository;
use App\Repository\ObjetRepository;
use App\Repository\ClasseRepository;
use App\Repository\LibraryRepository;
use App\Repository\AvantageRepository;
use App\Repository\CompetenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReglesController extends AbstractController
{
    /**
     * @Route("/regles", name="regles")
     */
    public function viewRegles(RuleRepository $ruleRepository,  ClasseRepository $classeRepository, LibraryRepository $libraryRepository): Response
    {
        $rulesBases = $ruleRepository->findBases();
        $librariesBases = $libraryRepository->findBases();

        $rulesAnnexes = $ruleRepository->findAnnexes();
        $librariesAnnexes = $libraryRepository->findAnnexes();

        $annexes = array_merge($rulesAnnexes, $librariesAnnexes);
        $classes = $classeRepository->findAll();

        $sections = [];

        // Section 1 : Règles de Bases
        $sections[0]['name'] = "Règles de Bases";
        $sections[0]['entity'] = 'rule';
        $sections[0]['label_one'] = 'une Règle';
        $sections[0]['titleLight'] = 'Règles de ';
        $sections[0]['titleStrong'] = 'Bases';

        
        // Section 2 : Les Classes
        $sections[1]['name'] = "Classes";
        $sections[1]['entity'] = 'classe';
        $sections[1]['label_one'] = 'une Classe';
        $sections[1]['titleLight'] = 'Les ';
        $sections[1]['titleStrong'] = 'Classes';

        
        // Section 3 : Les Bibliothèques de Bases (Liste d'Objet de Règles comme les Avantages/Désavantages/Compétences/Equipement/Sorts/Kiho/Tatoo)
        $sections[2]['name'] = "Bibliothèques";
        $sections[2]['entity'] = 'library';
        $sections[2]['label_one'] = 'une Bibliothèque';
        $sections[2]['titleLight'] = 'Les ';
        $sections[2]['titleStrong'] = 'Bibliothèques';

        // Section 4 : Les Règles Annexes (Règles Simples Annexes ou Bibliothèques Annexes)
        $sections[3]['name'] = "Règles Annexes";
        $sections[3]['entity'] = 'rule';
        $sections[3]['label_one'] = 'une Règle';
        $sections[3]['titleLight'] = 'Règles ';
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
            'rulesBases' => $rulesBases,
            'librariesBases' => $librariesBases,
            'annexes' => $annexes,
            'classes' => $classes,
        ]);
    }

    /**
     * @Route("/regles/rule/{id}", name="regles_rule")
     */
    public function viewRule(Rule $rule, RuleRepository $ruleRepository, Request $request, AvantageRepository $avantageRepository, CompetenceRepository $competenceRepository, ObjetRepository $objetRepository): Response
    {
        $autresRules = $ruleRepository->findOthersSameType($rule->getId(), $rule->getBase());

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
     * @Route("/regles/library/{id}", name="regles_library")
     */
    public function viewLibrary(Library $library, LibraryRepository $libraryRepository, Request $request,
                                AvantageRepository $avantageRepository,
                                CompetenceRepository $competenceRepository,
                                SortRepository $sortRepository,
                                ObjetRepository $objetRepository): Response
    {
        // RECUPERATION des noms des champs de la librairie
        $tab_field_name = $library->getTabField();
        $subtab_field_name = $library->getSubTabField();
        $filter_field_name = $library->getFilterField();

        // RECUPERATION URL PARAMS
        $tab_url_param = $request->query->get('tab');
        $subtab_url_param = $request->query->get('subtab');
        $filter_url_param = $request->query->get('filter');
        $keyword_url_param = $request->query->get('keyword');

        // LIBRARY ALL ITEMS
        $entity_name = $library->getEntity();
        $items = ${ $entity_name . 'Repository' }->findAll();

        // TABS, SUBTABS initialize
        $tabs = [];
        $subtabs = [];
        $filters = [];
        $keywords = [];

        // TABS
        if ( !empty( $tab_field_name ) ) {
            foreach ( $items as $item )
            {
                $tabs[] = $item->{ 'get' .  ucfirst( $tab_field_name ) }();

                if (!empty($filter_field_name))
                {
                    $filters[] = $item->{ 'get' .  ucfirst( $filter_field_name ) }();
                }

            }
            $tabs = array_unique($tabs);
            $filters = array_unique($filters);

            if ($library->getNom() == 'Grimoire' )
                $tabs = ['MAGIE', 'MAHO', 'KIHO', 'TATOUAGE'];
        }

        // IF TAB SELECTED
        if ( !empty( $tab_url_param ) && !empty( $tab_field_name ) )
        {
            if ( $tab_url_param != 'all')
                $items = array_filter($items, function ($obj) use ($tab_url_param, $tab_field_name) { return $obj->{ 'get' .  ucfirst( $tab_field_name ) }() == $tab_url_param; });

            if ( !empty( $subtab_field_name ) ) {

                foreach ( $items as $item )
                    $subtabs[] = $item->{ 'get' .  ucfirst( $subtab_field_name ) }();

                $subtabs = array_unique($subtabs);

                if ($library->getNom() == 'Avantages / Désavantages')
                    $subtabs = ['MENTAL','PHYSIQUE','SOCIAL','SPIRITUEL', 'MATERIEL'];
                elseif ($library->getNom() == 'Armurerie' && $tab_url_param == 'ARME' )
                    $subtabs = ['ÉPÉE', 'HAST', 'LANCE', 'LOURDE', 'BÂTON', 'ARC', 'CHAÎNE', 'COUTEAU', 'ÉVENTAIL'];
                elseif ($library->getNom() == 'Grimoire' && $tab_url_param == 'MAGIE' )
                    $subtabs = ['AIR', 'EAU', 'FEU', 'TERRE', 'VIDE', 'UNIVERSEL'];

                $subtab_first = $subtabs[0];

            }

            // IF FILTER KEYWORD EXISTS
            $keyword2_field_name = $library->getKeyword2Field();
            $keyword3_field_name = $library->getKeyword3Field();
            $keyword1_field_name = $library->getKeyword1Field();
            
            $keywords = [];
  
            foreach ( $items as $item ) {

                if ( !empty($keyword1_field_name) && !empty( $item->{ 'get' .  ucfirst( $keyword1_field_name ) }() ) )
                {
                    $keywords[] = $item->{ 'get' .  ucfirst( $keyword1_field_name ) }();
                }
    
                if ( !empty($keyword2_field_name) && !empty( $item->{ 'get' .  ucfirst( $keyword2_field_name ) }() ) )
                {
                    $keywords[] = $item->{ 'get' .  ucfirst( $keyword2_field_name ) }();
                }
                
                if ( !empty($keyword3_field_name) && !empty( $item->{ 'get' .  ucfirst( $keyword3_field_name ) }() ) )
                {
                    $keywords[] = $item->{ 'get' .  ucfirst( $keyword3_field_name ) }();
                }
            }

            $keywords = array_unique($keywords);
            sort($keywords);

            if ( !empty($subtab_field_name) && empty( $subtab_url_param ) )
                $items = [];

            // IF SUB TAB SELECTED
            if ( !empty( $subtab_url_param ) && !empty( $subtab_field_name ) && $subtab_url_param != 'all' && $subtab_url_param != 'first' )
            {
                $items = array_filter($items, function ($obj) use ($subtab_url_param, $subtab_field_name) {
                    return $obj->{ 'get' .  ucfirst( $subtab_field_name ) }() == $subtab_url_param; });
            }
            elseif ( $subtab_url_param == 'first' && !empty($subtab_first) )
            {
                $items = array_filter($items, function ($obj) use ($subtab_first, $subtab_field_name) {
                    return $obj->{ 'get' .  ucfirst( $subtab_field_name ) }() == $subtab_first; });
                $request->query->set('subtab', $subtab_first);
            }
        }

        // IF FILTER SELECTED
        if ( !empty( $filter_url_param ) && !empty( $filter_field_name ) )
            $items = array_filter($items, function ($obj) use ($filter_url_param, $filter_field_name) { return $obj->{ 'get' .  ucfirst( $filter_field_name ) }() == $filter_url_param; });

        // IF KEYWORD FILTER SELECTED
        if ( !empty( $keyword_url_param ) && ( !empty( $keyword1_field_name ) || !empty( $keyword2_field_name ) || !empty( $keyword3_field_name ) ) )
        {
            $filtered_items = [];
            foreach ( $items as $item )
            {
                if ( !empty( $item->{ 'get' . ucfirst( $keyword1_field_name ) }() )  && $item->{ 'get' . ucfirst( $keyword1_field_name ) }() == $keyword_url_param )
                    $filtered_items[] = $item;
                elseif (!empty( $item->{ 'get' . ucfirst( $keyword2_field_name ) }() )  && $item->{ 'get' . ucfirst( $keyword2_field_name ) }() == $keyword_url_param )
                    $filtered_items[] = $item;
                elseif (!empty( $item->{ 'get' . ucfirst( $keyword3_field_name ) }() )  && $item->{ 'get' . ucfirst( $keyword3_field_name ) }() == $keyword_url_param )
                    $filtered_items[] = $item;
            }
            $items = $filtered_items;
        }

        // OTHER LIBRARIES
        $otherLibraries = $libraryRepository->findOthersSameType($library->getId(), $library->getBase());

        // VIEW
        return $this->render('regles/library.html.twig', [
            'library' => $library,
            'nom' => $library->getNom(),
            'un_element' => $library,
            'entity' => 'library',
            'category' => 'regles',
            'otherLibraries' => $otherLibraries,
            'items' => $items,
            'tabs' => $tabs,
            'subtabs' => $subtabs,
            'filters' => $filters,
            'keywords' => $keywords,
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