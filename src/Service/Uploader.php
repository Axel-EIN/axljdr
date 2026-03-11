<?php
namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class Uploader extends AbstractController
{
    private $targetDirectory;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
        'video/mp4',
        'video/webm',
    ];

    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'pdf',
        'mp4', 'webm',
    ];

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $fichierForm, $prefix, $categorie): ?string
    {
        $mimeType = $this->detectRealMimeType($fichierForm);
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            $this->addFlash('danger', 'Type de fichier non autorisé (' . $mimeType . ')');
            return null;
        }

        $extension = $this->resolveExtension($fichierForm, $mimeType);
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $this->addFlash('danger', 'Extension de fichier non autorisée (' . $extension . ')');
            return null;
        }

        $accents = array(
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A',
            'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O',
            'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B',
            'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o',
            'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', ' '=>'-' );

        $prefix = strtr( $prefix, $accents );
        $prefix = strtolower($prefix);

        $nouveau_nomFichier = $prefix . '.' . $extension;

        try
        {
            $fichierForm->move($this->getTargetDirectory($categorie), $nouveau_nomFichier);
        }
        catch (FileException $e)
        {
            $this->addFlash('danger', 'Le fichier n\'a pu être téléversé');
            return null;
        }

        return $nouveau_nomFichier;
    }

    /**
     * Détecte le type MIME réel du fichier via finfo (analyse du contenu binaire, pas le header client).
     */
    private function detectRealMimeType(UploadedFile $fichier): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($fichier->getPathname());
    }

    /**
     * Résout l'extension à partir du MIME type réel détecté, pas celui déclaré par le client.
     */
    private function resolveExtension(UploadedFile $fichier, string $mimeType): ?string
    {
        $mimeTypes = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($mimeType);

        if (!empty($extensions)) {
            return $extensions[0];
        }

        return $fichier->guessExtension();
    }

    public function getTargetDirectory($categorie)
    {
        return $this->targetDirectory . '/' . $categorie;
    }
}
