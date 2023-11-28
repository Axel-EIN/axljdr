<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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

    public function titreRelief($titre)
    {
        // Cette fonction prend une chaîne de caractère et met du relief en ajoutant des balises span
        // qui vont réduire la taille des determinants et des mot de liaisons
    
        $search = ' à ';
        $replace = '<span class=title-small> &nbsp;à&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Le ';
        $replace = '<span class=title-small>Le&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' le ';
        $replace = '<span class=title-small> &nbsp;le&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'La ';
        $replace = '<span class=title-small>La&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' la ';
        $replace = '<span class=title-small> &nbsp;la&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Les ';
        $replace = '<span class=title-small>Les&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' les ';
        $replace = '<span class=title-small> &nbsp;les&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Du ';
        $replace = '<span class=title-small>Du&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' du ';
        $replace = '<span class=title-small> &nbsp;du&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'De ';
        $replace = '<span class=title-small>De&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' de ';
        $replace = '<span class=title-small> &nbsp;de&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Des ';
        $replace = '<span class=title-small>Des&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' des ';
        $replace = '<span class=title-small> &nbsp;des&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Dans ';
        $replace = '<span class=title-small>Dans&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' dans ';
        $replace = '<span class=title-small> &nbsp;dans&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'l\'';
        $replace = '<span class=title-small>l\'&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);

        $search = 'L\'';
        $replace = '<span class=title-small>L\'</span>';
        $titre = str_replace($search,$replace,$titre);

        $search = ' et ';
        $replace = ' <span class=title-small>et</span> ';
        $titre = str_replace($search,$replace,$titre);

        $search = ' son ';
        $replace = ' <span class=title-small>son</span> ';
        $titre = str_replace($search,$replace,$titre);
    
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
                  . '<img class="ml-1 img-24 align-text-bottom" src="/assets/img/ui/koku.png" alt="image pièce koku" title="Koku" /><br>';
            $reste = $reste % 50;
        }

        if ( $reste >= 10 ) {
            $html = $html . '<strong class="align-middle">' . floor($reste / 10) . '</strong>'
                          . '<img class="ml-1 img-18 align-middle" src="/assets/img/ui/bu.png" alt="image pièce bu" title="Bu" /><br>';
            $reste = $reste % 10;
        }

        if ( $reste > 0 ) {
            $html = $html . '<strong class="text-small align-middle">' . $reste . '</strong>'
                          . '<img class="ml-1 img-14 align-middle" src="/assets/img/ui/zeni.png" alt="image pièce zeni" title="Zeni" />';
        }
    
        return $html;
    }

    public function calculerJet($jet, $force)
    {
        // Cette fonction calcule le score moyen selon le jet

        $jet = explode('g', $jet);
        $jet[0] += $force;

        $Lfactor = 1.1;
        $Gfactor = 5.2;

        // xG1
        if ($jet[1] == 1 && $jet[0] >= 5) {
            $Lfactor = 1.1;
            $Gfactor = 5.4;
        } elseif ($jet[1] == 1) {
            $Lfactor = 1.55;
            $Gfactor = 5.4;
        } elseif ($jet[1] == 2) {
            $Lfactor = 1.2;
            $Gfactor = 6.3;
        } elseif ($jet[1] == 3 && $jet[0] >= 7) {
            $Lfactor = 1.6;
            $Gfactor = 5.8;
        } elseif ($jet[1] == 3 ) {
            $Lfactor = 1.7;
            $Gfactor = 5.65;
        } elseif ($jet[1] >= 4 && $jet[0] >= 7) {
            $Lfactor = 1.6;
            $Gfactor = 5.8;
        } elseif ($jet[1] >= 4 ) {
            $Lfactor = 1.5;
            $Gfactor = 5.7;
        }

        $score = floor( $jet[0] * $Lfactor + $jet[1] * $Gfactor );
        return $score;
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