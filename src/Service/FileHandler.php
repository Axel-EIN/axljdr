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

    public function handle($file, $previousFile, $prefix, $folder, ?string $preset = null)
    {
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $folder)) {
            throw new \InvalidArgumentException('Nom de dossier invalide');
        }

        if (empty($file)) {
            $this->delete($previousFile, $folder);

            return null;
        }

        $newFilename = $this->uploader->upload($file, $prefix, $folder, $previousFile);

        if ($newFilename === null) {
            return $previousFile;
        }

        if (basename((string) $previousFile) !== $newFilename) {
            $this->delete($previousFile, $folder);
        }

        if ($preset !== null && $this->imageNormalizer->hasPreset($preset)) {
            $absolutePath = $this->getParameter('image_directory') . '/' . $folder . '/' . $newFilename;
            $normalizedPath = $this->imageNormalizer->normalize($absolutePath, $preset);

            if ($normalizedPath !== null) {
                $newFilename = basename($normalizedPath);
            }
        }

        $newPath = 'assets/img/' . $folder . '/' . $newFilename;

        if (!empty($previousFile) && $previousFile !== $newPath) {
            $this->nameTagger->replacePath($previousFile, $newPath);
        }

        return $newPath;
    }

    private function delete(?string $previousFile, string $folder): void
    {
        if (empty($previousFile)) {
            return;
        }

        $path = $this->getParameter('image_directory') . '/' . $folder . '/' . basename($previousFile);

        if (!is_dir($path)) {
            (new Filesystem())->remove(array_merge([$path], $this->imageNormalizer->reducedPaths($path)));
        }
    }
}
