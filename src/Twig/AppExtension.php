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
            new TwigFilter('sortByField', [$this, 'sortByField']),
        ];
    }

    public function titreRelief($titre)
    {
        // Cette fonction prend une chaîne de caractère et met du relief en ajoutant des balises span
        // qui vont réduire la taille des determinants et des mot de liaisons
    
        $search = ' à ';
        $replace = '<span class=petit> &nbsp;à&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Le ';
        $replace = '<span class=petit>Le&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' le ';
        $replace = '<span class=petit> &nbsp;le&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'La ';
        $replace = '<span class=petit>La&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' la ';
        $replace = '<span class=petit> &nbsp;la&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Les ';
        $replace = '<span class=petit>Les&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' les ';
        $replace = '<span class=petit> &nbsp;les&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Du ';
        $replace = '<span class=petit>Du&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' du ';
        $replace = '<span class=petit> &nbsp;du&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'De ';
        $replace = '<span class=petit>De&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' de ';
        $replace = '<span class=petit> &nbsp;de&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Des ';
        $replace = '<span class=petit>Des&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' des ';
        $replace = '<span class=petit> &nbsp;des&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'Dans ';
        $replace = '<span class=petit>Dans&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = ' dans ';
        $replace = '<span class=petit> &nbsp;dans&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);
    
        $search = 'l\'';
        $replace = '<span class=petit>l\'&nbsp; </span>';
        $titre = str_replace($search,$replace,$titre);

        $search = 'L\'';
        $replace = '<span class=petit>L\'</span>';
        $titre = str_replace($search,$replace,$titre);

        $search = ' et ';
        $replace = ' <span class=petit>et</span> ';
        $titre = str_replace($search,$replace,$titre);

        $search = ' son ';
        $replace = ' <span class=petit>son</span> ';
        $titre = str_replace($search,$replace,$titre);
    
        return $titre;
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