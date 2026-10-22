<?php
namespace App\Service;

use App\Entity\Development;
use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;
use App\Repository\PersonnageRepository;
use App\Repository\LieuRepository;

class Baliseur
{
    public const CHAMPS = [
        Personnage::class  => ['description'],
        Scene::class       => ['texte'],
        Lieu::class        => ['description', 'regionDesc'],
        Development::class => ['content'],
    ];

    private const LIEN_PERSONNAGE = '#<a class="tag-character .*><img class="character-portrait small" .* \/> (.*)<\/a>#Ui';
    private const LIEN_LIEU = '#<a class="tag-location .*><img class="location-icon-small" .* \/> (.*)<\/a>#Ui';

    private $persoRepo;
    private $lieuRepo;

    public function __construct(PersonnageRepository $personnageRepository, LieuRepository $lieuRepository)
    {
        $this->persoRepo = $personnageRepository;
        $this->lieuRepo = $lieuRepository;
    }

    public function baliser(?string $texte): ?string
    {
        return empty($texte) ? $texte : $this->baliserLieux($this->baliserPersonnages($texte));
    }

    public function debaliser(?string $texte): ?string
    {
        return empty($texte) ? $texte : $this->debaliserLieux($this->debaliserPersonnages($texte));
    }

    public function rebaliser(?string $texte, ?string $ancien): ?string
    {
        if (empty($texte)) {
            return $texte;
        }

        $liens = $this->liensDe($ancien);

        return preg_replace_callback(
            '#\[[^\]]*\]|\{[^}]*\}#',
            fn (array $marqueur) => in_array($marqueur[0], $liens, true) ? $this->baliser($marqueur[0]) : $marqueur[0],
            $texte
        );
    }

    public function baliserPersonnages($texte)
    {
        $tableau = [];

        preg_match_all('#\[(.*)\]#Ui', $texte, $tableau);

        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#\[(.*)\]#Ui');
        $tableau_remplacement = [];

        foreach ($tableau[1] as $key => $un_match) {
            $personnage_trouve = $this->persoRepo->findOneBy(array('prenom' => $un_match));

            if ($personnage_trouve != null) {
                if($personnage_trouve->getIcone())
                    $icone_personnage = '../../' . $personnage_trouve->getIcone();
                else {
                    if ($personnage_trouve->getGenre() == 'M')
                        $icone_personnage = '../../assets/placeholders/na_perso_icon_M.jpg';
                    else if ($personnage_trouve->getGenre() == 'F')
                        $icone_personnage = '../../assets/placeholders/na_perso_icon_F.jpg';
                    else
                        $icone_personnage = '../../assets/placeholders/na_perso_icon.jpg';
                }
                $tableau_remplacement[] =
                    '<a class="tag-character hover-zoom-quick character-icon bg-background pr-1 pt-0 pb-0 pl-0" href="../../personnages/profil/' . $personnage_trouve->getId() . '">'
                                . '<img class="character-portrait small" src="../../' . $icone_personnage
                                . '" alt="Icône personnage" /> ' . $personnage_trouve->getPrenom() . '</a>';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }

    public function debaliserPersonnages($texte)
    {
        $tableau = [];

        preg_match_all(self::LIEN_PERSONNAGE, $texte, $tableau);

        $tableau_de_regexp = array_fill(0, count($tableau[1]), self::LIEN_PERSONNAGE);
        $tableau_remplacement = [];

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

        preg_match_all('#\{(.*)\}#Ui', $texte, $tableau);

        $tableau_de_regexp = array_fill(0, count($tableau[1]), '#\{(.*)\}#Ui');
        $tableau_remplacement = [];

        foreach ($tableau[1] as $key => $un_match) {
            $lieu_trouve = $this->lieuRepo->findOneBy(array('nom' => $un_match));

            if ($lieu_trouve != null) {

                if ($lieu_trouve->getIcone())
                    $icone_lieu = '../../' . $lieu_trouve->getIcone();
                else
                    $icone_lieu = '../../assets/placeholders/na_lieu_ico.png';

                $tableau_remplacement[] =
                    '<a class="tag-location hover-zoom-quick location-icon bg-background pr-1 pt-0 pb-0 pl-0" href="../../world/lieu/' . $lieu_trouve->getId() . '">'
                                . '<img class="location-icon-small" src="../../' . $icone_lieu
                                . '" alt="Icône Lieu" title="Voir ' . $lieu_trouve->getNom() .'" /> ' . $lieu_trouve->getNom() . '</a>';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }

    public function debaliserLieux($texte)
    {
        $tableau = [];

        preg_match_all(self::LIEN_LIEU, $texte, $tableau);

        $tableau_de_regexp = array_fill(0, count($tableau[1]), self::LIEN_LIEU);
        $tableau_remplacement = [];

        foreach ($tableau[1] as $key => $un_match) {
            $lieu_trouve = $this->lieuRepo->findOneBy(array('nom' => $un_match));
            if ($lieu_trouve != null) {
                $tableau_remplacement[] = '{' . $lieu_trouve->getNom() . '}';
            } else
                $tableau_remplacement[] = $un_match;
        }

        return preg_replace($tableau_de_regexp, $tableau_remplacement, $texte, 1);
    }

    private function liensDe(?string $texte): array
    {
        if (empty($texte)) {
            return [];
        }

        preg_match_all(self::LIEN_PERSONNAGE, $texte, $personnages);
        preg_match_all(self::LIEN_LIEU, $texte, $lieux);

        return array_merge(
            array_map(fn (string $prenom) => '[' . $prenom . ']', $personnages[1]),
            array_map(fn (string $nom) => '{' . $nom . '}', $lieux[1])
        );
    }
}
