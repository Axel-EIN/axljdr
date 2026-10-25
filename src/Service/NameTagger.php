<?php
namespace App\Service;

use App\Entity\Development;
use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;
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

    private const CHARACTER_LINK = '#<a class="tag-character .*><img class="character-portrait small" .* \/> (.*)<\/a>#Ui';
    private const LOCATION_LINK = '#<a class="tag-location .*><img class="location-icon-small" .* \/> (.*)<\/a>#Ui';

    private $characterRepository;
    private $locationRepository;
    private $entityManager;

    public function __construct(PersonnageRepository $characterRepository, LieuRepository $locationRepository, EntityManagerInterface $entityManager)
    {
        $this->characterRepository = $characterRepository;
        $this->locationRepository = $locationRepository;
        $this->entityManager = $entityManager;
    }

    public function defaultIcon($element): string
    {
        if ($element instanceof Lieu) {
            return 'assets/placeholders/na_lieu_ico.png';
        }

        $gender = $element->getGenre();

        return 'assets/placeholders/na_perso_icon' . (in_array($gender, ['M', 'F'], true) ? '_' . $gender : '') . '.jpg';
    }

    public function replaceWithDefaultIcon($element): int
    {
        return empty($element->getIcone()) ? 0 : $this->replacePath($element->getIcone(), $this->defaultIcon($element));
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
        return empty($text) ? $text : $this->tagLocations($this->tagCharacters($text));
    }

    public function untag(?string $text): ?string
    {
        return empty($text) ? $text : $this->untagLocations($this->untagCharacters($text));
    }

    public function retag(?string $text, ?string $previous): ?string
    {
        if (empty($text)) {
            return $text;
        }

        $links = $this->linksIn($previous);

        return preg_replace_callback(
            '#\[[^\]]*\]|\{[^}]*\}#',
            fn (array $marker) => in_array($marker[0], $links, true) ? $this->tag($marker[0]) : $marker[0],
            $text
        );
    }

    public function tagCharacters($text)
    {
        $matches = [];

        preg_match_all('#\[(.*)\]#Ui', $text, $matches);

        $patterns = array_fill(0, count($matches[1]), '#\[(.*)\]#Ui');
        $replacements = [];

        foreach ($matches[1] as $match) {
            $character = $this->characterRepository->findOneBy(array('prenom' => $match));

            if ($character != null) {
                $characterIcon = '../../' . ($character->getIcone() ?: $this->defaultIcon($character));
                $replacements[] =
                    '<a class="tag-character hover-zoom-quick character-icon bg-background pr-1 pt-0 pb-0 pl-0" href="../../characters/profile/' . $character->getId() . '">'
                                . '<img class="character-portrait small" src="../../' . $characterIcon
                                . '" alt="Icône personnage" /> ' . $character->getPrenom() . '</a>';
            } else
                $replacements[] = $match;
        }

        return preg_replace($patterns, $replacements, $text, 1);
    }

    public function untagCharacters($text)
    {
        $matches = [];

        preg_match_all(self::CHARACTER_LINK, $text, $matches);

        $patterns = array_fill(0, count($matches[1]), self::CHARACTER_LINK);
        $replacements = [];

        foreach ($matches[1] as $match) {
            $character = $this->characterRepository->findOneBy(array('prenom' => $match));
            if ($character != null) {
                $replacements[] = '[' . $character->getPrenom() . ']';
            } else
                $replacements[] = $match;
        }

        return preg_replace($patterns, $replacements, $text, 1);
    }

    public function tagLocations($text)
    {
        $matches = [];

        preg_match_all('#\{(.*)\}#Ui', $text, $matches);

        $patterns = array_fill(0, count($matches[1]), '#\{(.*)\}#Ui');
        $replacements = [];

        foreach ($matches[1] as $match) {
            $location = $this->locationRepository->findOneBy(array('nom' => $match));

            if ($location != null) {

                $locationIcon = '../../' . ($location->getIcone() ?: $this->defaultIcon($location));

                $replacements[] =
                    '<a class="tag-location hover-zoom-quick location-icon bg-background pr-1 pt-0 pb-0 pl-0" href="../../world/location/' . $location->getId() . '">'
                                . '<img class="location-icon-small" src="../../' . $locationIcon
                                . '" alt="Icône Lieu" title="Voir ' . $location->getNom() .'" /> ' . $location->getNom() . '</a>';
            } else
                $replacements[] = $match;
        }

        return preg_replace($patterns, $replacements, $text, 1);
    }

    public function untagLocations($text)
    {
        $matches = [];

        preg_match_all(self::LOCATION_LINK, $text, $matches);

        $patterns = array_fill(0, count($matches[1]), self::LOCATION_LINK);
        $replacements = [];

        foreach ($matches[1] as $match) {
            $location = $this->locationRepository->findOneBy(array('nom' => $match));
            if ($location != null) {
                $replacements[] = '{' . $location->getNom() . '}';
            } else
                $replacements[] = $match;
        }

        return preg_replace($patterns, $replacements, $text, 1);
    }

    private function linksIn(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        preg_match_all(self::CHARACTER_LINK, $text, $characters);
        preg_match_all(self::LOCATION_LINK, $text, $locations);

        return array_merge(
            array_map(fn (string $firstName) => '[' . $firstName . ']', $characters[1]),
            array_map(fn (string $name) => '{' . $name . '}', $locations[1])
        );
    }
}
