<?php

namespace App\Twig;

use App\Entity\Access;
use App\Entity\FactionType;
use App\Entity\Monnaie;
use App\Entity\Status;
use App\Service\CurrentPlayer;
use App\Service\ElementUrl;
use App\Service\EntityRegistry;
use App\Service\ImageBrightness;
use App\Service\ImageNormalizer;
use App\Service\TableFilter;
use App\Service\Visibility;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $visibility;
    private $elementUrl;
    private $imageBrightness;
    private $tableFilter;
    private $currentPlayer;
    private $imageNormalizer;
    private $packages;

    public function __construct(Visibility $visibility, ElementUrl $elementUrl, ImageBrightness $imageBrightness, TableFilter $tableFilter, CurrentPlayer $currentPlayer, ImageNormalizer $imageNormalizer, Packages $packages)
    {
        $this->visibility = $visibility;
        $this->elementUrl = $elementUrl;
        $this->imageBrightness = $imageBrightness;
        $this->tableFilter = $tableFilter;
        $this->currentPlayer = $currentPlayer;
        $this->imageNormalizer = $imageNormalizer;
        $this->packages = $packages;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('relief', [$this, 'titleRelief']),
            new TwigFilter('list_items', [$this, 'linesToList']),
            new TwigFilter('pricer', [$this, 'formatPrice']),
            new TwigFilter('expected_roll', [$this, 'expectedRoll']),
            new TwigFilter('sortByField', [$this, 'sortByField']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('roll_map', [$this, 'getRollMap']),
            new TwigFunction('element_state', [$this->visibility, 'state']),
            new TwigFunction('element_readable', [$this->visibility, 'isReadable']),
            new TwigFunction('element_unseen', [$this->visibility, 'isUnseen']),
            new TwigFunction('element_individual', [$this->visibility, 'isIndividuallyRevealed']),
            new TwigFunction('element_listed', [$this->visibility, 'isListed']),
            new TwigFunction('element_fresh', [$this->visibility, 'isFresh']),
            new TwigFunction('element_sort', [$this->visibility, 'sortByAccess']),
            new TwigFunction('element_key', [EntityRegistry::class, 'keyOf']),
            new TwigFunction('element_url', [$this->elementUrl, 'of']),
            new TwigFunction('access_label', [$this, 'accessLabel']),
            new TwigFunction('status_label', [$this, 'statusLabel']),
            new TwigFunction('faction_label', [$this, 'factionLabel']),
            new TwigFunction('image_bright', [$this->imageBrightness, 'isBright']),
            new TwigFunction('image_small', [$this->imageNormalizer, 'reducedOrOriginal']),
            new TwigFunction('image_preset', [$this->imageNormalizer, 'getPreset']),
            new TwigFunction('image_srcset', [$this, 'imageSrcset']),
            new TwigFunction('table_filters', [$this->tableFilter, 'build']),
            new TwigFunction('table_value', [$this->tableFilter, 'valueOf']),
            new TwigFunction('navigating_character', [$this->currentPlayer, 'navigatingCharacter']),
            new TwigFunction('is_gm', [$this->currentPlayer, 'seesAll']),
        ];
    }

    public function imageSrcset(?string $path): ?string
    {
        $reduced = $this->imageNormalizer->existingReduced($path);
        if ($reduced === null) {
            return null;
        }

        $candidates = [];
        foreach ([$reduced, $path] as $image) {
            $width = $this->imageNormalizer->width($image);
            if ($width === null) {
                return null;
            }

            $candidates[] = $this->packages->getUrl($image) . ' ' . $width . 'w';
        }

        return implode(', ', $candidates);
    }

    public function accessLabel($access, bool $manualUnlock = false): string
    {
        $label = Access::LABELS[$access] ?? '';

        return $manualUnlock && Access::needsUnlock((int) $access) ? $label . ' · ' . Access::MANUAL_LABEL : $label;
    }

    public function statusLabel($status): string
    {
        return Status::LABELS[$status] ?? '';
    }

    public function factionLabel($type): string
    {
        return FactionType::LABELS[$type] ?? '';
    }

    public function getRollMap()
    {
        return [
            1  => [1 => 6.10],
            2  => [1 => 8.37,  2 => 12.31],
            3  => [1 => 9.68,  2 => 15.28, 3 => 18.32],
            4  => [1 => 10.66, 2 => 17.37, 3 => 21.90, 4 => 24.54],
            5  => [1 => 11.34, 2 => 19.03, 3 => 24.58, 4 => 28.45, 5 => 30.49],
            6  => [1 => 12.17, 2 => 20.31, 3 => 26.49, 4 => 31.28, 5 => 34.63, 6 => 36.54],
            7  => [1 => 12.73, 2 => 21.42, 3 => 28.22, 4 => 33.94, 5 => 38.02, 6 => 41.05, 7 => 42.83],
            8  => [1 => 13.21, 2 => 22.38, 3 => 29.72, 4 => 35.69, 5 => 40.54, 6 => 44.57, 7 => 47.22, 8 => 50.04],
            9  => [1 => 13.75, 2 => 23.27, 3 => 30.90, 4 => 37.36, 5 => 42.95, 6 => 47.40, 7 => 50.76, 8 => 53.34, 9 => 55.16],
            10 => [1 => 14.24, 2 => 23.99, 3 => 32.04, 4 => 38.96, 5 => 44.93, 6 => 50.00, 7 => 54.07, 8 => 57.34, 9 => 59.62, 10 => 60.90],
        ];
    }

    private const SMALL_WORDS = ['à', 'a', 'le', 'la', 'les', 'du', 'de', 'des', 'dans', 'et', 'son'];

    private const ELISIONS = ["l'" => "l'&nbsp;", "L'" => "L'", "d'" => "d'&nbsp;", "D'" => "D'"];

    public function titleRelief($title)
    {
        $title = preg_replace(
            '/(?<!\p{L})(' . implode('|', self::SMALL_WORDS) . ')\s+/ui',
            '<span class="small-word">$1&nbsp;</span>',
            $title
        );

        foreach (self::ELISIONS as $elision => $rendered) {
            $title = str_replace($elision, '<span class="small-word">' . $rendered . '</span>', $title);
        }

        return $title;
    }

    public function linesToList($text)
    {
        $lines = explode("\n", $text);

        $listedText = "";
        foreach($lines as $line)
        {
          $listedText .= "<li>" . $line . "</li>";
        }

        return $listedText;
    }

    public function formatPrice($price)
    {

        $remainder = $price;
        $html = '';

        if ( $remainder >= Monnaie::ZENI_PAR_KOKU  ) {
            $html = '<strong class="text-medium">' . floor($remainder / Monnaie::ZENI_PAR_KOKU) . '</strong>'
                  . '<img class="ml-1 img-24 align-text-bottom" src="/assets/icons/money/koku.png" alt="image pièce koku" title="Koku" /><br>';
            $remainder = $remainder % Monnaie::ZENI_PAR_KOKU;
        }

        if ( $remainder >= Monnaie::ZENI_PAR_BU ) {
            $html = $html . '<strong class="align-middle">' . floor($remainder / Monnaie::ZENI_PAR_BU) . '</strong>'
                          . '<img class="ml-1 img-18 align-middle" src="/assets/icons/money/bu.png" alt="image pièce bu" title="Bu" /><br>';
            $remainder = $remainder % Monnaie::ZENI_PAR_BU;
        }

        if ( $remainder > 0 ) {
            $html = $html . '<strong class="text-small align-middle">' . $remainder . '</strong>'
                          . '<img class="ml-1 img-14 align-middle" src="/assets/icons/money/zeni.png" alt="image pièce zeni" title="Zeni" />';
        }
    
        return $html;
    }

    public function expectedRoll($rollOrDamage, $traitOrBonusDice = 0)
    {
      $rollParts = explode('g', (string) $rollOrDamage);

      if (count($rollParts) < 2)
        return 0;

      $rolledDice = (int) $rollParts[0];
      $keptDice = (int) $rollParts[1];

      if ($keptDice < 1 )
        return 0;

      if ($traitOrBonusDice >= 1)
        $rolledDice += $traitOrBonusDice;

      if ($rolledDice < $keptDice)
        $keptDice = $rolledDice;

      $bonus = 0;
      if ($rolledDice > 10) {
        $bonus += ($rolledDice - 10) * 2;
        $rolledDice = 10;
      }

      if ($keptDice > 10) {
        $bonus += ($keptDice - 10) * 3;
        $keptDice = 10;
      }

      $map = $this->getRollMap();
      return ($map[$rolledDice][$keptDice] ?? 0) + $bonus;
    }

    public function sortByField($content, $sort_by, $direction = 'asc'){
        if (is_a($content, 'Doctrine\ORM\PersistentCollection')) {
            $content = $content->toArray();
        }
        if (!is_array($content)) {
            throw new \InvalidArgumentException('Variable passed to the sortByField filter is not an array');
        } elseif (count($content) < 1) { return $content; } else { @usort($content, function ($a, $b) use ($sort_by, $direction) { $flip = ($direction === 'desc') ? -1 : 1; if (is_array($a)) $a_sort_value = $a[$sort_by]; else if (method_exists($a, 'get' . ucfirst($sort_by))) $a_sort_value = $a->{'get' . ucfirst($sort_by)}();
                else
                    $a_sort_value = $a->$sort_by;
                if (is_array($b))
                    $b_sort_value = $b[$sort_by];
                else if (method_exists($b, 'get' . ucfirst($sort_by)))
                    $b_sort_value = $b->{'get' . ucfirst($sort_by)}();
                else
                    $b_sort_value = $b->$sort_by;
                if ($a_sort_value == $b_sort_value) {
                    return 0;
                } else if ($a_sort_value > $b_sort_value) {
                    return (1 * $flip);
                } else {
                    return (-1 * $flip);
                }
            });
        }
        return $content;
    }

    public function getName()
    {
        return 'sortbyfield_extension';
    }
}