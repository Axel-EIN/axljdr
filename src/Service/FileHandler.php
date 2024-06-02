<?php
namespace App\Service;

use App\Service\Uploader;
use App\Service\ImageNormalizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileHandler extends AbstractController
{
    private $uploader;
    private $imageNormalizer;

    public function __construct(Uploader $uploader, ImageNormalizer $imageNormalizer)
    {
        $this->uploader = $uploader;
        $this->imageNormalizer = $imageNormalizer;
    }

    public function handle($fichier, $ancienFichier, $prefix, $dossier, ?string $preset = null)
    {
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $dossier)) {
            throw new \InvalidArgumentException('Nom de dossier invalide');
        }

        if (empty($fichier)) { // Cas de la suppression seulement
            $this->supprimer($ancienFichier, $dossier);

            return null;
        }

        $nouveauNomFichier = $this->uploader->upload($fichier, $prefix, $dossier);

        if ($nouveauNomFichier === null) {
            return $ancienFichier;
        }

        // Après l'upload seulement : un type refusé ne doit pas détruire le fichier
        // en place. Et jamais à nom identique, l'upload vient de l'écraser.
        if (basename((string) $ancienFichier) !== $nouveauNomFichier) {
            $this->supprimer($ancienFichier, $dossier);
        }

        if ($preset !== null && $this->imageNormalizer->hasPreset($preset)) {
            $cheminAbsolu = $this->getParameter('image_directory') . '/' . $dossier . '/' . $nouveauNomFichier;
            $this->imageNormalizer->normalize($cheminAbsolu, $preset);
        }

        return 'assets/img/' . $dossier . '/' . $nouveauNomFichier;
    }

    private function supprimer(?string $ancienFichier, string $dossier): void
    {
        if (empty($ancienFichier)) {
            return;
        }

        $chemin = $this->getParameter('image_directory') . '/' . $dossier . '/' . basename($ancienFichier);

        if (!is_dir($chemin)) {
            (new Filesystem())->remove($chemin);
        }
    }
}