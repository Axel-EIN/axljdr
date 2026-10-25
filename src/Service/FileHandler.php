<?php
namespace App\Service;

use App\Service\Uploader;
use App\Service\ImageNormalizer;
use App\Service\NameTagger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileHandler extends AbstractController
{
    private $uploader;
    private $imageNormalizer;
    private $nameTagger;

    public function __construct(Uploader $uploader, ImageNormalizer $imageNormalizer, NameTagger $nameTagger)
    {
        $this->uploader = $uploader;
        $this->imageNormalizer = $imageNormalizer;
        $this->nameTagger = $nameTagger;
    }

    public function handle($fichier, $ancienFichier, $prefix, $dossier, ?string $preset = null)
    {
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $dossier)) {
            throw new \InvalidArgumentException('Nom de dossier invalide');
        }

        if (empty($fichier)) {
            $this->supprimer($ancienFichier, $dossier);

            return null;
        }

        $nouveauNomFichier = $this->uploader->upload($fichier, $prefix, $dossier, $ancienFichier);

        if ($nouveauNomFichier === null) {
            return $ancienFichier;
        }

        if (basename((string) $ancienFichier) !== $nouveauNomFichier) {
            $this->supprimer($ancienFichier, $dossier);
        }

        if ($preset !== null && $this->imageNormalizer->hasPreset($preset)) {
            $cheminAbsolu = $this->getParameter('image_directory') . '/' . $dossier . '/' . $nouveauNomFichier;
            $cheminNormalise = $this->imageNormalizer->normalize($cheminAbsolu, $preset);

            if ($cheminNormalise !== null) {
                $nouveauNomFichier = basename($cheminNormalise);
            }
        }

        $nouveauChemin = 'assets/img/' . $dossier . '/' . $nouveauNomFichier;

        if (!empty($ancienFichier) && $ancienFichier !== $nouveauChemin) {
            $this->nameTagger->replacePath($ancienFichier, $nouveauChemin);
        }

        return $nouveauChemin;
    }

    private function supprimer(?string $ancienFichier, string $dossier): void
    {
        if (empty($ancienFichier)) {
            return;
        }

        $chemin = $this->getParameter('image_directory') . '/' . $dossier . '/' . basename($ancienFichier);

        if (!is_dir($chemin)) {
            (new Filesystem())->remove(array_merge([$chemin], $this->imageNormalizer->reducedPaths($chemin)));
        }
    }
}
