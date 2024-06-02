<?php
namespace App\Service;

use App\Service\Uploader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileHandler extends AbstractController
{
    private $uploader;

    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public function handle($fichier, $ancienFichier, $prefix, $dossier)
    {

        if ($fichier && !empty($fichier))
        {
            $nouveauNomFichier = $this->uploader->upload($fichier, $prefix, $dossier);
            $nouveauCheminRelatif = 'assets/img/' . $dossier . '/' . $nouveauNomFichier;

             // Gestion de l'ancien fichier
             if ($ancienFichier && !empty($ancienFichier))
             {
                $ancienNomFichier = basename($ancienFichier);
                $ancienCheminComplet = $this->getParameter('image_directory') . '/' . $dossier . '/' . $ancienNomFichier;

                if (is_dir($ancienCheminComplet) == false) { // si ce n'est pas un dossier
                    $filesystem = new Filesystem();
                    $filesystem->remove($ancienCheminComplet);
                }
            }

            return $nouveauCheminRelatif;

        } elseif ($ancienFichier && !empty($ancienFichier)) {
                $ancienNomFichier = basename($ancienFichier);
                $ancienCheminComplet = $this->getParameter('image_directory') . '/' . $dossier . '/' . $ancienNomFichier;

                if (is_dir($ancienCheminComplet) == false) { // si ce n'est pas un dossier
                    $filesystem = new Filesystem();
                    $filesystem->remove($ancienCheminComplet);
                }
        }
    }
}