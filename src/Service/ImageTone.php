<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;

class ImageTone
{
    private const SAMPLE = 32;
    private const THRESHOLD = 0.3;
    private const HUE_STEP = 30;

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
        return $this->measured('luminance', $path, 0.0, function (array $pixels) {
            $total = 0;
            foreach ($pixels as [$red, $green, $blue]) {
                $total += 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
            }

            return $total / (count($pixels) * 255);
        });
    }

    public function color(?string $path): ?string
    {
        return $this->measured('dominant_color', $path, null, function (array $pixels) {
            $buckets = [];
            foreach ($pixels as $pixel) {
                $max = max($pixel);
                $spread = $max - min($pixel);
                if ($spread === 0) {
                    continue;
                }

                $weight = $spread / 255;
                $bucket = intdiv((int) $this->hue($pixel, $max, $spread), self::HUE_STEP);
                $buckets[$bucket]['weight'] = ($buckets[$bucket]['weight'] ?? 0) + $weight;
                foreach ($pixel as $channel => $value) {
                    $buckets[$bucket]['sums'][$channel] = ($buckets[$bucket]['sums'][$channel] ?? 0) + $value * $weight;
                }
            }

            if ($buckets === []) {
                $buckets[] = ['weight' => count($pixels), 'sums' => array_map(fn ($channel) => array_sum(array_column($pixels, $channel)), [0, 1, 2])];
            }

            $dominant = array_reduce($buckets, fn ($best, $bucket) => $bucket['weight'] > ($best['weight'] ?? 0) ? $bucket : $best, []);

            return vsprintf('#%02x%02x%02x', array_map(fn ($sum) => (int) round($sum / $dominant['weight']), $dominant['sums']));
        });
    }

    private function hue(array $pixel, int $max, int $spread): float
    {
        [$red, $green, $blue] = $pixel;
        $hue = match ($max) {
            $red => fmod(($green - $blue) / $spread, 6),
            $green => ($blue - $red) / $spread + 2,
            default => ($red - $green) / $spread + 4,
        };

        return fmod($hue * 60 + 360, 360);
    }

    private function measured(string $metric, ?string $path, $default, callable $measure)
    {
        $file = $this->publicDirectory . '/' . ltrim((string) $path, '/');
        if (!$path || !is_file($file)) {
            return $default;
        }

        $key = $metric . '_' . md5($file . filemtime($file));

        return $this->cache->get($key, function () use ($file, $default, $measure) {
            $pixels = $this->pixels($file);

            return $pixels === [] ? $default : $measure($pixels);
        });
    }

    private function pixels(string $file): array
    {
        $source = @imagecreatefromstring(file_get_contents($file));
        if ($source === false) {
            return [];
        }

        $sample = imagecreatetruecolor(self::SAMPLE, self::SAMPLE);
        imagecopyresampled($sample, $source, 0, 0, 0, 0, self::SAMPLE, self::SAMPLE, imagesx($source), imagesy($source));

        $pixels = [];
        for ($y = 0; $y < self::SAMPLE; $y++) {
            for ($x = 0; $x < self::SAMPLE; $x++) {
                $rgb = imagecolorat($sample, $x, $y);
                $pixels[] = [($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF];
            }
        }

        return $pixels;
    }
}
