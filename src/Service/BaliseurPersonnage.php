<?php
namespace App\Service;

use App\Entity\Personnage;
use App\Repository\PersonnageRepository;

class BaliseurPersonnage
{
    private $persoRepo;

    public function __construct(PersonnageRepository $personnageRepository)
    {
        $this->persoRepo = $personnageRepository;
    }

    public function baliserPersonnage($texte)
    {
        $tableau = [];

        // Capture tout les mots entre [ ]
        preg_match_all('#\[(.*)\]#Ui', $texte, $tableau);

        // Crée un tableau de regexp au nbr de matchs
        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#\[(.*)\]#Ui');
        $tableau_remplacement = [];

        // Remplace par un lien HTML vers la fiche du personnage
        foreach ($tableau[1] as $key => $un_match) {
            $personnage_trouve = $this->persoRepo->findOneBy(array('prenom' => $un_match));

            if ($personnage_trouve != null) {
                $tableau_remplacement[] =
                    '<a class="perso-img" href="../../personnages/profil/' . $personnage_trouve->getId() . '">'
                                . '<img src="../../' . $personnage_trouve->getIcone()
                                . '" alt="Icône du personnage" /> ' . $personnage_trouve->getPrenom() . '</a>';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }

    public function debaliserPersonnage($texte)
    {
        // Capture tout les prenoms entre les balises a img
        preg_match_all('#<a .*><img .*/> (.*)<\/a>#Ui', $texte, $tableau);

        // Crée un tableau de regexp au nbr de matchs
        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#<a .*><img .*/> (.*)<\/a>#Ui');
        $tableau_remplacement = [];

        // Remplace par des crochets
        foreach ($tableau[1] as $key => $un_match) {
            $personnage_trouve = $this->persoRepo->findOneBy(array('prenom' => $un_match));
            if ($personnage_trouve != null) {
                $tableau_remplacement[] = '[' . $personnage_trouve->getPrenom() . ']';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }
}