<?php

namespace App\Controller;

use App\Entity\Objet;
use App\Entity\Rule;
use App\Entity\Ecole;
use App\Entity\Classe;
use App\Entity\Library;
use App\Entity\Personnage;
use App\Repository\RuleRepository;
use App\Repository\SortRepository;
use App\Repository\EcoleRepository;
use App\Repository\ObjetRepository;
use App\Repository\ClasseRepository;
use App\Repository\LibraryRepository;
use App\Repository\AvantageRepository;
use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Visibility;

class RulesController extends AbstractController
{
    use VisibilityTrait;

    /**
     * @Route("/rules", name="rules")
     */
    public function index(RuleRepository $ruleRepository, ClasseRepository $classRepository, LibraryRepository $libraryRepository): Response
    {
        $baseRules = $ruleRepository->findBases();
        $baseLibraries = $libraryRepository->findBases();
        $annexes = $ruleRepository->findAnnexes();
        $classes = $classRepository->findAll();

        $sections = [];

        $sections[0]['name'] = "Règles de Base";
        $sections[0]['entity'] = 'rule';
        $sections[0]['label_one'] = 'une Règle';
        $sections[0]['titleLight'] = 'Règles de ';
        $sections[0]['titleStrong'] = 'Base';

        
        $sections[1]['name'] = "Classes";
        $sections[1]['entity'] = 'classe';
        $sections[1]['label_one'] = 'une Classe';
        $sections[1]['titleLight'] = 'Les ';
        $sections[1]['titleStrong'] = 'Classes';

        
        $sections[2]['name'] = "Bibliothèques";
        $sections[2]['entity'] = 'library';
        $sections[2]['label_one'] = 'une Bibliothèque';
        $sections[2]['titleLight'] = 'Les ';
        $sections[2]['titleStrong'] = 'Bibliothèques';

        $sections[3]['name'] = "Règles Annexes";
        $sections[3]['entity'] = 'rule';
        $sections[3]['label_one'] = 'une Règle';
        $sections[3]['titleLight'] = 'Règles ';
        $sections[3]['titleStrong'] = 'Annexes';

        $header_classname = 'rules';
        $header_up = "Mécaniques du Jeu";
        $header_down = "Règles";
        $category = 'rules';

        return $this->render('rules/index.html.twig', [
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category,
            'sections' => $sections,
            'base_rules' => $baseRules,
            'base_libraries' => $baseLibraries,
            'annexes' => $annexes,
            'classes' => $classes,
        ]);
    }

    /**
     * @Route("/rules/rule/{id}", name="rules_rule")
     */
    public function showRule(Rule $rule, RuleRepository $ruleRepository, Visibility $visibility): Response
    {
        if ($response = $this->accessGuard($visibility, $rule, 'rule', 'rules')) {
            return $response;
        }

        $otherRules = $ruleRepository->findOthersSameType($rule->getId(), $rule->getBase());

        return $this->render('rules/rule.html.twig', [
            'rule' => $rule,
            'nom' => $rule->getNom(),
            'entity' => 'rule',
            'category' => 'rules',
            'un_element' => $rule,
            'other_rules' => $otherRules
        ]);
    }

     /**
     * @Route("/rules/library/{id}", name="rules_library")
     */
    public function showLibrary(Library $library, LibraryRepository $libraryRepository, Request $request,
                                AvantageRepository $advantageRepository,
                                CompetenceRepository $skillRepository,
                                SortRepository $spellRepository,
                                ObjetRepository $objectRepository, Visibility $visibility): Response
    {
        if ($response = $this->accessGuard($visibility, $library, 'library', 'rules')) {
            return $response;
        }

        $tab_field_name = $library->getTabField();
        $subtab_field_name = $library->getSubTabField();
        $filter_field_name = $library->getFilterField();

        $tab_url_param = $request->query->get('tab');
        $subtab_url_param = $request->query->get('subtab');
        $filter_url_param = $request->query->get('filter');
        $keyword_url_param = $request->query->get('keyword');

        $entity_name = $library->getEntity();
        $repositories = [
            'avantage' => $advantageRepository,
            'competence' => $skillRepository,
            'sort' => $spellRepository,
            'objet' => $objectRepository,
        ];
        $repository = $repositories[$entity_name] ?? throw $this->createNotFoundException();
        $items = $visibility->listedOnly($repository->findAll());

        $tabs = [];
        $subtabs = [];
        $filters = [];
        $keywords = [];

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

        $discounts = [];

        if ( $entity_name == 'avantage' ) {
            $discount_getters = [
                'clan' => ['label' => 'Discount Clan/Faction', 'getter' => 'getDiscountClans'],
                'class' => ['label' => 'Discount Classe', 'getter' => 'getDiscountClasses'],
            ];

            foreach ( $discount_getters as $param => $discount ) {
                $options = [];
                foreach ( $items as $item )
                    foreach ( $item->{ $discount['getter'] }() as $element )
                        $options[$element->getId()] = $element->getNom();

                asort($options);

                if ( !empty( $options ) )
                    $discounts[$param] = ['label' => $discount['label'], 'options' => $options];
            }

            foreach ( $discount_getters as $param => $discount ) {
                $discount_url_param = $request->query->get($param);

                if ( !empty( $discount_url_param ) )
                    $items = array_filter($items, fn($item) => in_array($discount_url_param, array_map(fn($element) => $element->getId(), $item->{ $discount['getter'] }())));
            }
        }

        if ( !empty( $filter_url_param ) && !empty( $filter_field_name ) )
            $items = array_filter($items, function ($obj) use ($filter_url_param, $filter_field_name) { return $obj->{ 'get' .  ucfirst( $filter_field_name ) }() == $filter_url_param; });

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

        $otherLibraries = $libraryRepository->findOthersSameType($library->getId(), $library->getBase());

        return $this->render('rules/library-detail.html.twig', [
            'library' => $library,
            'nom' => $library->getNom(),
            'un_element' => $library,
            'entity' => 'library',
            'category' => 'rules',
            'other_libraries' => $otherLibraries,
            'items' => $items,
            'tabs' => $tabs,
            'subtabs' => $subtabs,
            'filters' => $filters,
            'keywords' => $keywords,
            'discounts' => $discounts,
        ]);
    }

    /**
     * @Route("/rules/object/{id}", name="rules_object")
     */
    public function showObject(Objet $object, ObjetRepository $objectRepository, Visibility $visibility): Response
    {
        if ($response = $this->accessGuard($visibility, $object, 'objet', 'rules')) {
            return $response;
        }

        $otherObjects = array_filter(
            $objectRepository->findBy(['categorie' => $object->getCategorie()], ['nom' => 'ASC']),
            fn (Objet $other) => $other->getId() !== $object->getId() && $visibility->isListed($other)
        );

        return $this->render('rules/object-detail.html.twig', [
            'un_element' => $object,
            'nom' => $object->getNom(),
            'entity' => 'objet',
            'category' => 'rules',
            'other_objects' => array_slice(array_values($otherObjects), 0, 12),
        ]);
    }

    /**
     * @Route("/rules/class/{id}", name="rules_class")
     */
    public function showClass(Classe $class, EcoleRepository $dojoRepository, ClasseRepository $classRepository): Response
    {
        $classes = $classRepository->findAll();
        $playerCharacters = $this->playerCharacters($class->getPersonnages());
        $classDojos = $dojoRepository->findByClasseSorted($class->getId());
        shuffle($playerCharacters);

        return $this->render('rules/class-detail.html.twig', [
            'classe' => $class,
            'nom' => $class->getNom(),
            'entity' => 'classe',
            'category' => 'rules',
            'un_element' => $class,
            'player_characters' => $playerCharacters,
            'class_dojos' => $classDojos,
            'classes' => $classes
        ]);
    }

    /**
     * @Route("/rules/dojo/{id}", name="rules_dojo")
     */
    public function showDojo(Ecole $dojo, EcoleRepository $dojoRepository, Visibility $visibility): Response
    {
        if ($response = $this->accessGuard($visibility, $dojo, 'ecole', 'rules')) {
            return $response;
        }

        $otherDojos = $dojoRepository->findAllByClanExceptOne($dojo->getClan()->getId(), $dojo->getId());

        return $this->render('rules/dojo-detail.html.twig', [
            'ecole' => $dojo,
            'nom' => $dojo->getNom(),
            'entity' => 'ecole',
            'category' => 'rules',
            'un_element' => $dojo,
            'other_dojos' => $otherDojos,
            'player_characters' => $this->playerCharacters($dojo->getPersonnages()),
        ]);
    }

    private function playerCharacters(Collection $characters): array
    {
        return $characters->filter(fn (Personnage $character) => $character->getEstPj())->getValues();
    }
}