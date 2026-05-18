<?php

namespace App\Service;

class ImageNormalizer
{
    public const PRESETS = [
        'landscape1080' => ['width' => 1920, 'height' => 1080, 'mode' => 'cover'],
        'landscape720'  => ['width' => 1280, 'height' => 720,  'mode' => 'cover'],
        'fourthird900'  => ['width' => 1200, 'height' => 900,  'mode' => 'cover'],
        'square320'     => ['width' => 320,  'height' => 320,  'mode' => 'cover'],
        'square240'     => ['width' => 240,  'height' => 240,  'mode' => 'cover'],
        'square192'     => ['width' => 192,  'height' => 192,  'mode' => 'cover'],
        'vertical450'   => ['width' => 450,  'height' => 800,  'mode' => 'cover'],
    ];

    private const JPEG_QUALITY = 85;
    private const WEBP_QUALITY = 85;
    private const PNG_COMPRESSION = 6;

    public function hasPreset(string $name): bool
    {
        return isset(self::PRESETS[$name]);
    }

    public function getPreset(string $name): array
    {
        if (!$this->hasPreset($name)) {
            throw new \InvalidArgumentException("Preset image inconnu : '$name'");
        }
        return self::PRESETS[$name];
    }

    /**
     * Réécrit l'image en place aux dimensions du preset. Mode 'cover' = remplit
     * le cadre en cropant les bords excédentaires (centré).
     */
    public function normalize(string $absolutePath, string $presetName): bool
    {
        $preset = $this->getPreset($presetName);

        $info = @getimagesize($absolutePath);
        if ($info === false) {
            return false;
        }

        [$srcW, $srcH] = $info;
        $type = $info[2];

        $src = $this->loadImage($absolutePath, $type);
        if ($src === null) {
            return false;
        }

        $dstW = $preset['width'];
        $dstH = $preset['height'];

        $dst = imagecreatetruecolor($dstW, $dstH);

        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP || $type === IMAGETYPE_GIF) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
            imagealphablending($dst, true);
        }

        // Calcule le rectangle source en mode "cover" centré
        [$srcX, $srcY, $copyW, $copyH] = $this->coverCrop($srcW, $srcH, $dstW, $dstH);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $dstW, $dstH, $copyW, $copyH);

        $ok = $this->saveImage($dst, $absolutePath, $type);

        imagedestroy($src);
        imagedestroy($dst);

        return $ok;
    }

    private function coverCrop(int $srcW, int $srcH, int $dstW, int $dstH): array
    {
        $srcRatio = $srcW / $srcH;
        $dstRatio = $dstW / $dstH;

        if ($srcRatio > $dstRatio) {
            // Source plus large que la cible : on crope sur les côtés
            $copyH = $srcH;
            $copyW = (int) round($srcH * $dstRatio);
            $srcX = (int) round(($srcW - $copyW) / 2);
            $srcY = 0;
        } else {
            // Source plus haute que la cible : on crope en haut/bas
            $copyW = $srcW;
            $copyH = (int) round($srcW / $dstRatio);
            $srcX = 0;
            $srcY = (int) round(($srcH - $copyH) / 2);
        }

        return [$srcX, $srcY, $copyW, $copyH];
    }

    private function loadImage(string $path, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path) ?: null;
            case IMAGETYPE_PNG:  return @imagecreatefrompng($path)  ?: null;
            case IMAGETYPE_GIF:  return @imagecreatefromgif($path)  ?: null;
            case IMAGETYPE_WEBP: return @imagecreatefromwebp($path) ?: null;
            default:             return null;
        }
    }

    private function saveImage($resource, string $path, int $type): bool
    {
        switch ($type) {
            case IMAGETYPE_JPEG: return imagejpeg($resource, $path, self::JPEG_QUALITY);
            case IMAGETYPE_PNG:  return imagepng($resource, $path, self::PNG_COMPRESSION);
            case IMAGETYPE_GIF:  return imagegif($resource, $path);
            case IMAGETYPE_WEBP: return imagewebp($resource, $path, self::WEBP_QUALITY);
            default:             return false;
        }
    }
}
