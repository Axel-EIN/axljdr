<?php
namespace App\Service;

use App\Entity\Library;
use App\Repository\AvantageRepository;
use App\Repository\CompetenceRepository;
use App\Repository\LibraryRepository;
use App\Repository\LoreRepository;
use App\Repository\ObjetRepository;
use App\Repository\RuleRepository;
use App\Repository\SortRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PartLinker
{
    private const TAB_LABELS = [
        'BUGEI' => 'Bugei',
        'COUTEAU' => 'Couteaux',
        'DIVERS' => 'Divers',
        'HAST' => 'Hast',
        'MARCHAND' => 'Marchand',
        'NINJUTSU' => 'Ninjutsu',
        'OBJET' => 'Objets divers',
    ];

    private const PLURAL_TABS = ['advantage', 'skill', 'object'];
    private const PLURAL_SUBTABS = ['object'];

    private const ICONS = [
        'rule' => 'fa-solid fa-scroll',
        'lore' => 'fa-solid fa-book-open',
        'library' => 'fa-solid fa-book',
    ];

    private $repositories;
    private $itemRepositories;
    private $visibility;
    private $elementUrl;
    private $router;

    public function __construct(
        RuleRepository $ruleRepository,
        LoreRepository $loreRepository,
        LibraryRepository $libraryRepository,
        AvantageRepository $advantageRepository,
        CompetenceRepository $skillRepository,
        SortRepository $spellRepository,
        ObjetRepository $objectRepository,
        Visibility $visibility,
        ElementUrl $elementUrl,
        UrlGeneratorInterface $router
    ) {
        $this->repositories = [
            'rule' => $ruleRepository,
            'lore' => $loreRepository,
            'library' => $libraryRepository,
        ];
        $this->itemRepositories = [
            'advantage' => $advantageRepository,
            'skill' => $skillRepository,
            'spell' => $spellRepository,
            'object' => $objectRepository,
        ];
        $this->visibility = $visibility;
        $this->elementUrl = $elementUrl;
        $this->router = $router;
    }

    public function choices(): array
    {
        $choices = [
            'Règles de base' => [],
            'Règles annexes' => [],
            'Lores' => [],
            'Bibliothèques' => [],
        ];

        foreach ($this->repositories['rule']->findBy([], ['numero' => 'ASC']) as $rule) {
            $choices[$rule->getBase() ? 'Règles de base' : 'Règles annexes'][$rule->getNom()] = 'rule:' . $rule->getId();
        }

        foreach ($this->repositories['lore']->findAllOrdered() as $lore) {
            $choices['Lores'][$lore->getNom()] = 'lore:' . $lore->getId();
        }

        foreach ($this->repositories['library']->findBy([], ['numero' => 'ASC']) as $library) {
            $choices['Bibliothèques'][$library->getNom()] = 'library:' . $library->getId();

            foreach ($this->tabs($library) as $tab) {
                $choices['Bibliothèques'][$library->getNom() . ' › ' . $this->tabLabel($library, $tab)] = 'library:' . $library->getId() . ':' . $tab;
            }
        }

        return array_filter($choices);
    }

    public function resolve(array $references): array
    {
        $links = [];

        foreach ($references as $reference) {
            [$key, $id, $tab] = array_pad(explode(':', $reference, 3), 3, null);
            $element = isset($this->repositories[$key]) ? $this->repositories[$key]->find((int) $id) : null;

            if ($element === null || !$this->visibility->isListed($element)) {
                continue;
            }

            $links[] = $tab === null
                ? ['label' => $element->getNom(), 'url' => $this->elementUrl->of($element), 'icon' => self::ICONS[$key]]
                : ['label' => $element->getNom() . ' › ' . $this->tabLabel($element, $tab), 'url' => $this->tabUrl($element, $tab), 'icon' => self::ICONS[$key]];
        }

        return $links;
    }

    public function tabLabel(Library $library, string $value, bool $subtab = false): string
    {
        $plurals = $subtab ? self::PLURAL_SUBTABS : self::PLURAL_TABS;
        $label = mb_strtoupper(mb_substr($value, 0, 1)) . mb_strtolower(mb_substr($value, 1));

        return self::TAB_LABELS[mb_strtoupper($value)] ?? $label . (in_array($library->getEntity(), $plurals, true) ? 's' : '');
    }

    public function tabUrl(Library $library, string $tab): string
    {
        $parameters = ['id' => $library->getId(), 'tab' => $tab];

        if (!empty($library->getSubTabField())) {
            $parameters['subtab'] = $library->getMixable() ? 'all' : 'first';
        }

        return $this->router->generate('rules_library', $parameters);
    }

    private function tabs(Library $library): array
    {
        $field = $library->getTabField();
        $repository = $this->itemRepositories[$library->getEntity()] ?? null;

        if (empty($field) || $repository === null) {
            return [];
        }

        $tabs = array_map(fn ($item) => $item->{'get' . ucfirst($field)}(), $repository->findAll());

        return array_values(array_unique(array_filter($tabs)));
    }
}
