<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;

class ImageBrightness
{
    private const SAMPLE = 32;
    private const THRESHOLD = 0.3;

    private $cache;
    private $publicDirectory;

    public function __construct(CacheInterface $cache, string $publicDirectory)
    {
        $this->cache = $cache;
        $this->publicDirectory = $publicDirectory;
    }

    public function isBright(?string $path): bool
    {
        return $this->luminance($path) > self::THRESHOLD;
    }

    public function luminance(?string $path): float
    {
        $file = $this->publicDirectory . '/' . ltrim((string) $path, '/');
        if (!$path || !is_file($file)) {
            return 0.0;
        }

        $key = 'luminance_' . md5($file . filemtime($file));

        return $this->cache->get($key, function () use ($file) {
            return $this->measure($file);
        });
    }

    private function measure(string $file): float
    {
        $source = @imagecreatefromstring(file_get_contents($file));
        if ($source === false) {
            return 0.0;
        }

        $sample = imagecreatetruecolor(self::SAMPLE, self::SAMPLE);
        imagecopyresampled($sample, $source, 0, 0, 0, 0, self::SAMPLE, self::SAMPLE, imagesx($source), imagesy($source));

        $total = 0;
        for ($x = 0; $x < self::SAMPLE; $x++) {
            for ($y = 0; $y < self::SAMPLE; $y++) {
                $rgb = imagecolorat($sample, $x, $y);
                $total += 0.2126 * (($rgb >> 16) & 0xFF) + 0.7152 * (($rgb >> 8) & 0xFF) + 0.0722 * ($rgb & 0xFF);
            }
        }

        return $total / (self::SAMPLE * self::SAMPLE * 255);
    }
}
