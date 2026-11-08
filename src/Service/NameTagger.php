<?php
namespace App\Service;

use App\Entity\Development;
use App\Entity\Lieu;
use App\Entity\Objet;
use App\Entity\Personnage;
use App\Entity\Scene;
use App\Repository\ObjetRepository;
use App\Repository\PersonnageRepository;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;

class NameTagger
{
    public const FIELDS = [
        Personnage::class  => ['description'],
        Scene::class       => ['texte'],
        Lieu::class        => ['description', 'regionDesc'],
        Development::class => ['content'],
    ];

    private const KINDS = [
        'character' => [
            'marker' => '#\[([^\]]*)\](?!\()#Ui',
            'link' => '#<a class="tag-character .*><img class="character-portrait small" .* \/> (.*)<\/a>#Ui',
            'syntax' => '[%s]',
            'field' => 'prenom',
            'getter' => 'getPrenom',
            'path' => 'characters/profile/',
            'anchor' => 'character-icon',
            'image' => 'character-portrait small',
            'alt' => 'Icône personnage',
            'title' => false,
        ],
        'location' => [
            'marker' => '#\{(.*)\}#Ui',
            'link' => '#<a class="tag-location .*><img class="location-icon-small" .* \/> (.*)<\/a>#Ui',
            'syntax' => '{%s}',
            'field' => 'nom',
            'getter' => 'getNom',
            'path' => 'world/location/',
            'anchor' => 'location-icon',
            'image' => 'location-icon-small',
            'alt' => 'Icône Lieu',
            'title' => true,
        ],
        'object' => [
            'marker' => '#\(\((.*)\)\)#Ui',
            'link' => '#<a class="tag-object .*><img class="location-icon-small" .* \/> (.*)<\/a>#Ui',
            'syntax' => '((%s))',
            'field' => 'nom',
            'getter' => 'getNom',
            'path' => 'rules/object/',
            'anchor' => 'location-icon',
            'image' => 'location-icon-small',
            'alt' => 'Icône objet',
            'title' => true,
        ],
    ];

    private $repositories;
    private $entityManager;

    public function __construct(PersonnageRepository $characterRepository, LieuRepository $locationRepository, ObjetRepository $objectRepository, EntityManagerInterface $entityManager)
    {
        $this->repositories = [
            'character' => $characterRepository,
            'location' => $locationRepository,
            'object' => $objectRepository,
        ];
        $this->entityManager = $entityManager;
    }

    public function defaultIcon($element): string
    {
        if ($element instanceof Lieu) {
            return 'assets/placeholders/na_lieu_ico.png';
        }

        if ($element instanceof Objet) {
            return 'assets/placeholders/na_icon.png';
        }

        $gender = $element->getGenre();

        return 'assets/placeholders/na_perso_icon' . (in_array($gender, ['M', 'F'], true) ? '_' . $gender : '') . '.jpg';
    }

    public function replaceWithDefaultIcon($element): int
    {
        $icon = $this->iconOf($element);

        return empty($icon) ? 0 : $this->replacePath($icon, $this->defaultIcon($element));
    }

    public function replacePath(string $previous, string $new): int
    {
        $connection = $this->entityManager->getConnection();
        $rows = 0;

        foreach (self::FIELDS as $entity => $fields) {
            $metadata = $this->entityManager->getClassMetadata($entity);

            foreach ($fields as $field) {
                $column = $metadata->getColumnName($field);
                $query = sprintf('UPDATE %1$s SET %2$s = REPLACE(%2$s, ?, ?) WHERE %2$s LIKE ?', $metadata->getTableName(), $column);
                $rows += $connection->executeStatement($query, [$previous, $new, '%' . $previous . '%']);
            }
        }

        return $rows;
    }

    public function tag(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        foreach (array_keys(self::KINDS) as $kind) {
            $text = $this->tagKind($kind, $text);
        }

        return $text;
    }

    public function untag(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        foreach (array_keys(self::KINDS) as $kind) {
            $text = $this->untagKind($kind, $text);
        }

        return $text;
    }

    public function retag(?string $text, ?string $previous): ?string
    {
        if (empty($text)) {
            return $text;
        }

        $links = $this->linksIn($previous);

        return preg_replace_callback(
            '#\[[^\]]*\](?!\()|\{[^}]*\}|\(\([^)]*\)\)#',
            fn (array $marker) => in_array($marker[0], $links, true) ? $this->tag($marker[0]) : $marker[0],
            $text
        );
    }

    private function tagKind(string $kind, string $text): string
    {
        $settings = self::KINDS[$kind];

        return preg_replace_callback($settings['marker'], function (array $marker) use ($kind, $settings) {
            $element = $this->repositories[$kind]->findOneBy([$settings['field'] => $marker[1]]);

            return $element ? $this->link($kind, $element) : $marker[1];
        }, $text);
    }

    private function untagKind(string $kind, string $text): string
    {
        $settings = self::KINDS[$kind];

        return preg_replace_callback($settings['link'], function (array $link) use ($kind, $settings) {
            $element = $this->repositories[$kind]->findOneBy([$settings['field'] => $link[1]]);

            return $element ? sprintf($settings['syntax'], $element->{$settings['getter']}()) : $link[1];
        }, $text);
    }

    private function link(string $kind, $element): string
    {
        $settings = self::KINDS[$kind];
        $name = $element->{$settings['getter']}();
        $icon = '../../' . ($this->iconOf($element) ?: $this->defaultIcon($element));
        $title = $settings['title'] ? ' title="Voir ' . $name . '"' : '';

        return '<a class="tag-' . $kind . ' hover-zoom-quick ' . $settings['anchor'] . ' bg-background pr-1 pt-0 pb-0 pl-0" href="../../' . $settings['path'] . $element->getId() . '">'
            . '<img class="' . $settings['image'] . '" src="../../' . $icon . '" alt="' . $settings['alt'] . '"' . $title . ' /> ' . $name . '</a>';
    }

    private function iconOf($element): ?string
    {
        return $element instanceof Objet ? $element->getIcon() : $element->getIcone();
    }

    private function linksIn(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $links = [];

        foreach (self::KINDS as $settings) {
            preg_match_all($settings['link'], $text, $matches);

            foreach ($matches[1] as $name) {
                $links[] = sprintf($settings['syntax'], $name);
            }
        }

        return $links;
    }
}
