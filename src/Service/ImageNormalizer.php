<?php

namespace App\Service;

class ImageNormalizer
{
    public const PRESETS = [
        'landscape1080' => ['width' => 1920, 'height' => 1080, 'format' => IMAGETYPE_JPEG, 'reduced' => ['width' => 960, 'height' => 540]],
        'landscape720'  => ['width' => 1280, 'height' => 720, 'format' => IMAGETYPE_JPEG, 'reduced' => ['width' => 640, 'height' => 360]],
        'fourthird900'  => ['width' => 1200, 'height' => 900, 'format' => IMAGETYPE_JPEG, 'reduced' => ['width' => 640, 'height' => 480]],
        'square320'     => ['width' => 320,  'height' => 320, 'reduced' => ['width' => 128, 'height' => 128]],
        'square240'     => ['width' => 240,  'height' => 240, 'format' => IMAGETYPE_JPEG],
        'square192'     => ['width' => 192,  'height' => 192],
        'vertical450'   => ['width' => 450,  'height' => 800, 'format' => IMAGETYPE_JPEG, 'reduced' => ['width' => 360, 'height' => 640]],
    ];

    private const EXTENSIONS = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    private const REDUCED_SUFFIX = '-reduced';

    private const JPEG_QUALITY = 85;
    private const WEBP_QUALITY = 85;
    private const PNG_COMPRESSION = 6;

    private $publicDirectory;

    public function __construct(string $publicDirectory)
    {
        $this->publicDirectory = $publicDirectory;
    }

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

    public function normalize(string $absolutePath, string $presetName): ?string
    {
        $preset = $this->getPreset($presetName);

        $target = $this->transform($absolutePath, $preset['width'], $preset['height'], $preset['format'] ?? null);

        if ($target !== null) {
            $this->reduce($target, $presetName);
        }

        return $target;
    }

    public function matchesPreset(string $absolutePath, string $presetName): bool
    {
        $preset = $this->getPreset($presetName);

        $info = @getimagesize($absolutePath);
        if ($info === false) {
            return false;
        }

        return $info[0] === $preset['width']
            && $info[1] === $preset['height']
            && (!isset($preset['format']) || $info[2] === $preset['format']);
    }

    public function reduce(string $absolutePath, string $presetName): bool
    {
        $preset = $this->getPreset($presetName);
        $reduced = $preset['reduced'] ?? null;
        if ($reduced === null) {
            return false;
        }

        $info = @getimagesize($absolutePath);
        if ($info === false || !isset(self::EXTENSIONS[$info[2]])) {
            return false;
        }

        $format = $preset['format'] ?? $info[2];

        return $this->render($absolutePath, $this->withExtension($absolutePath, $format, self::REDUCED_SUFFIX), $reduced['width'], $reduced['height'], $format);
    }

    public function reducedPaths(string $path): array
    {
        return array_map(function (int $format) use ($path) {
            return $this->withExtension($path, $format, self::REDUCED_SUFFIX);
        }, array_keys(self::EXTENSIONS));
    }

    public function existingReduced(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        foreach ($this->reducedPaths($path) as $reduced) {
            if (is_file($this->publicDirectory . '/' . ltrim($reduced, '/'))) {
                return $reduced;
            }
        }

        return null;
    }

    public function reducedOrOriginal(?string $path): ?string
    {
        return $this->existingReduced($path) ?? $path;
    }

    private function transform(string $absolutePath, int $dstW, int $dstH, ?int $format): ?string
    {
        $info = @getimagesize($absolutePath);
        if ($info === false) {
            return null;
        }

        $format = $format ?? $info[2];
        $target = $format === $info[2] ? $absolutePath : $this->withExtension($absolutePath, $format);

        if (!$this->render($absolutePath, $target, $dstW, $dstH, $format)) {
            return null;
        }

        if ($target !== $absolutePath) {
            unlink($absolutePath);
        }

        return $target;
    }

    private function withExtension(string $path, int $format, string $suffix = ''): string
    {
        return preg_replace('/\.[^.\/]+$/', '', $path) . $suffix . '.' . self::EXTENSIONS[$format];
    }

    private function render(string $source, string $target, int $dstW, int $dstH, int $format): bool
    {
        $info = @getimagesize($source);
        if ($info === false) {
            return false;
        }

        [$srcW, $srcH] = $info;

        $src = $this->loadImage($source, $info[2]);
        if ($src === null) {
            return false;
        }

        $dst = imagecreatetruecolor($dstW, $dstH);

        if ($format === IMAGETYPE_PNG || $format === IMAGETYPE_WEBP || $format === IMAGETYPE_GIF) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
            imagealphablending($dst, true);
        }

        [$srcX, $srcY, $copyW, $copyH] = $this->coverCrop($srcW, $srcH, $dstW, $dstH);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $dstW, $dstH, $copyW, $copyH);

        $ok = $this->saveImage($dst, $target, $format);

        imagedestroy($src);
        imagedestroy($dst);

        return $ok;
    }

    private function coverCrop(int $srcW, int $srcH, int $dstW, int $dstH): array
    {
        $srcRatio = $srcW / $srcH;
        $dstRatio = $dstW / $dstH;

        if ($srcRatio > $dstRatio) {
            $copyH = $srcH;
            $copyW = (int) round($srcH * $dstRatio);
            $srcX = (int) round(($srcW - $copyW) / 2);
            $srcY = 0;
        } else {
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
