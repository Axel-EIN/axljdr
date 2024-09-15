<?php
namespace App\Service;

use App\Repository\PersonnageRepository;
use App\Repository\LieuRepository;

class Baliseur
{
    private $persoRepo;
    private $lieuRepo;

    public function __construct(PersonnageRepository $personnageRepository, LieuRepository $lieuRepository)
    {
        $this->persoRepo = $personnageRepository;
        $this->lieuRepo = $lieuRepository;
    }

    public function baliserPersonnages($texte)
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
                    '<a class="hover-zoom-X2 character-icon bg-lightest pr-1 pt-0 pb-0 pl-0" href="../../personnages/profil/' . $personnage_trouve->getId() . '">'
                                . '<img class="character-icon-small" src="../../' . $personnage_trouve->getIcone()
                                . '" alt="Icône du personnage" title="' . $personnage_trouve->getPrenom() .'" /> ' . $personnage_trouve->getPrenom() . '</a>';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }

    public function debaliserPersonnages($texte)
    {
        // Capture tout les prenoms entre les balises a img
        preg_match_all('#<a .*><img class="character-icon-small" .* \/> (.*)<\/a>#Ui', $texte, $tableau);

        // Crée un tableau de regexp au nbr de matchs
        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#<a .*><img class="character-icon-small" .* \/> (.*)<\/a>#Ui');
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

    public function baliserLieux($texte)
    {
        $tableau = [];

        // Capture tout les mots entre [ ]
        preg_match_all('#\{(.*)\}#Ui', $texte, $tableau);

        // Crée un tableau de regexp au nbr de matchs
        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#\{(.*)\}#Ui');
        $tableau_remplacement = [];

        // Remplace par un lien HTML vers la fiche du Lieu
        foreach ($tableau[1] as $key => $un_match) {
            $lieu_trouve = $this->lieuRepo->findOneBy(array('nom' => $un_match));

            if ($lieu_trouve != null) {
                $tableau_remplacement[] =
                    '<a class="hover-zoom-X2 location-icon bg-lightest pr-1 pt-0 pb-0 pl-0" href="../../empire/lieu/' . $lieu_trouve->getId() . '">'
                                . '<img class="location-icon-small" src="../../assets/img/icons/icon-location.png'
                                . '" alt="Icône Lieu" title="Voir ' . $lieu_trouve->getNom() .'" /> ' . $lieu_trouve->getNom() . '</a>';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }

    public function debaliserLieux($texte)
    {
        // Capture tout les prenoms entre les balises a img
        preg_match_all('#<a .*><img class="location-icon-small" .* \/> (.*)<\/a>#Ui', $texte, $tableau);

        // Crée un tableau de regexp au nbr de matchs
        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#<a .*><img class="location-icon-small" .* \/> (.*)<\/a>#Ui');
        $tableau_remplacement = [];

        // Remplace par des crochets
        foreach ($tableau[1] as $key => $un_match) {
            $lieu_trouve = $this->lieuRepo->findOneBy(array('nom' => $un_match));
            if ($lieu_trouve != null) {
                $tableau_remplacement[] = '{' . $lieu_trouve->getNom() . '}';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }
}