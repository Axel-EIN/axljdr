<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('relief', [$this, 'titreRelief']),
            new TwigFilter('lister', [$this, 'listerRetourChariot']),
            new TwigFilter('pricer', [$this, 'distribuerPrix']),
            new TwigFilter('jeter', [$this, 'calculerJet']),
            new TwigFilter('sortByField', [$this, 'sortByField']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('jet_map', [$this, 'getJetMap']),
        ];
    }

    public function getJetMap()
    {
        // Table de correspondance des scores moyens pour chaque combinaison XgY
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

    /* Un mot outil et l'espace qui le suit sont reduits ensemble ; celui qui le
       precede reste au mot fort d'avant. */
    private const MOTS_OUTILS = ['à', 'le', 'la', 'les', 'du', 'de', 'des', 'dans', 'et', 'son'];

    /* L'elision prend de l'air en milieu de titre, reste collee en tete. */
    private const ELISIONS = ["l'" => "l'&nbsp;", "L'" => "L'"];

    public function titreRelief($titre)
    {
        // Le lookbehind ne consomme pas le separateur de gauche : deux mots
        // outils qui se suivent sont donc reduits tous les deux.
        $titre = preg_replace(
            '/(?<!\p{L})(' . implode('|', self::MOTS_OUTILS) . ')\s+/ui',
            '<span class="small-word">$1&nbsp;</span>',
            $titre
        );

        foreach (self::ELISIONS as $elision => $rendu) {
            $titre = str_replace($elision, '<span class="small-word">' . $rendu . '</span>', $titre);
        }

        return $titre;
    }

    public function listerRetourChariot($text)
    {
        // Cette fonction prend un texte et pour chaque retour chariot il va l'entourer de balise <li> pour créer une liste HTML
    
        $string = "line 1\nline 2\nline3";
        
        $lignes = explode("\n", $text);
        
        $listedText = "";
        foreach($lignes as $ligne)
        {
          $listedText .= "<li>" . $ligne . "</li>";
        }
        $listedText .= "";
    
        return $listedText;
    }

    public function distribuerPrix($prix)
    {
        // Cette fonction distribue le prix selon les différentes pièces de monnaie

        $reste = $prix;
        $html = '';

        if ( $reste >= 50  ) {
            $html = '<strong class="text-medium">' . floor($reste / 50) . '</strong>'
                  . '<img class="ml-1 img-24 align-text-bottom" src="/assets/icons/money/koku.png" alt="image pièce koku" title="Koku" /><br>';
            $reste = $reste % 50;
        }

        if ( $reste >= 10 ) {
            $html = $html . '<strong class="align-middle">' . floor($reste / 10) . '</strong>'
                          . '<img class="ml-1 img-18 align-middle" src="/assets/icons/money/bu.png" alt="image pièce bu" title="Bu" /><br>';
            $reste = $reste % 10;
        }

        if ( $reste > 0 ) {
            $html = $html . '<strong class="text-small align-middle">' . $reste . '</strong>'
                          . '<img class="ml-1 img-14 align-middle" src="/assets/icons/money/zeni.png" alt="image pièce zeni" title="Zeni" />';
        }
    
        return $html;
    }

    public function calculerJet($jetOuVD, $traitOuBonusD = 0)
    {
      $jetDecompose = explode('g', $jetOuVD); // On décompose le jet ou la VD (format XgY)
      $lancesDFinal = $jetDecompose[0];
      $gardesDFinal = $jetDecompose[1];

      // === Moins de 1 dé gardé
      if ($gardesDFinal < 1 )
        return 0;

      // === Trait ou Bonus au dés lancés présent
      if ($traitOuBonusD >= 1) // Si le trait ou les bonus aux dés sont supérieur à 1, alors on les ajoute au total des dés lancés
        $lancesDFinal += $traitOuBonusD;

      // === Plus de dés gardés que lancés
      if ($lancesDFinal < $gardesDFinal)
        $gardesDFinal = $lancesDFinal;

      // === Dés surnuméraires au dessus de 10
      $bonus = 0;
      if ($lancesDFinal > 10) {
        $bonus += ($lancesDFinal - 10) * 2;  // On donne un bonus de +2 par dés surnuméraires
        $lancesDFinal = 10; // On cap à 10 le nombre de dés lancés
      }

      if ($gardesDFinal > 10) {
        $bonus += ($gardesDFinal - 10) * 3; // On donne un bonus de +3 par dés surnuméraires
        $gardesDFinal = 10; // On cap à 10 le nombre de dés gardés
      }

      // === On fait correspondance le jet sur la matrice pour avoir le score moyen
      $map = $this->getJetMap();
      return ($map[$lancesDFinal][$gardesDFinal] ?? 0) + $bonus;
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