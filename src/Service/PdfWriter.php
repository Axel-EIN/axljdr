<?php

namespace App\Service;

class PdfWriter
{
    public const ALIGN_LEFT = 'left';
    public const ALIGN_CENTER = 'center';
    public const ALIGN_RIGHT = 'right';

    public const INK_DEFAULT = [0.10, 0.12, 0.24];
    public const INK_MUTED = [0.74, 0.76, 0.80];
    public const INK_TAINTED = [0.86, 0.21, 0.27];
    public const INK_AFFINITY = [0.16, 0.65, 0.27];

    private const PAGE_WIDTH = 595.28;
    private const PAGE_HEIGHT = 841.89;
    private const JPEG_QUALITY = 88;
    private const FIXED_OBJECTS = 2;
    private const FONT_OBJECTS = 2;

    private const WIDTHS = [
        false => [278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584,750,556,278,222,556,333,1000,556,556,333,1000,667,333,1000,278,611,278,278,222,222,333,333,350,556,1000,333,1000,500,333,944,278,500,667,278,333,556,556,556,556,260,556,333,737,370,556,584,0,737,552,400,549,333,333,333,576,537,333,333,333,365,556,834,834,834,611,667,667,667,667,667,667,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,500,556,556,556,556,278,278,278,278,556,556,556,556,556,556,556,549,611,556,556,556,556,500,556,500],
        true => [278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584,750,556,278,278,556,500,1000,556,556,333,1000,667,333,1000,278,611,278,278,278,278,500,500,350,556,1000,333,1000,556,333,944,278,500,667,278,333,556,556,556,556,280,556,333,737,370,556,584,0,737,552,400,549,333,333,333,576,556,333,333,333,365,556,834,834,834,611,722,722,722,722,722,722,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,556,556,556,556,556,278,278,278,278,611,611,611,611,611,611,611,549,611,611,611,611,611,556,611,556],
    ];

    private float $scale;
    private array $images = [];
    private array $pages = [''];
    private int $current = 0;

    public function __construct(int $canvasWidth)
    {
        $this->scale = self::PAGE_WIDTH / $canvasWidth;
    }

    public function newPage(): void
    {
        $this->pages[] = '';
        $this->current = count($this->pages) - 1;
    }

    public function background(string $path): void
    {
        $this->place($this->rasterize($path), 0, 0, (int) round(self::PAGE_WIDTH / $this->scale), (int) round(self::PAGE_HEIGHT / $this->scale));
    }

    public function overlay(string $path, int $x, int $y, ?int $width = null, ?int $height = null): void
    {
        $image = $this->rasterize($path, null, 0.5, true);

        if ($image === null) {
            return;
        }

        $this->place($image, $x, $y, $width ?? $image['width'], $height ?? $image['height']);
    }

    public function cover(string $path, int $x, int $y, int $width, int $height, float $verticalFocus = 0.5): void
    {
        $this->place($this->rasterize($path, $width / $height, $verticalFocus), $x, $y, $width, $height);
    }

    public function text(string $text, int $x, int $y, int $size, array $options = []): void
    {
        $text = trim($this->flatten($text));

        if ($text === '') {
            return;
        }

        $bold = (bool) ($options['bold'] ?? false);
        $align = $options['align'] ?? self::ALIGN_LEFT;
        $width = $options['width'] ?? null;
        $ink = $options['ink'] ?? self::INK_DEFAULT;

        if ($width !== null) {
            $text = $this->fit($text, $size, $bold, $width);
        }

        $span = $this->widthOf($text, $size, $bold);

        if ($align === self::ALIGN_CENTER) {
            $x -= (int) round($span / 2);
        } elseif ($align === self::ALIGN_RIGHT) {
            $x -= (int) round($span);
        }

        $this->pages[$this->current] .= sprintf(
            "BT %.3f %.3f %.3f rg /%s %.2f Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET\n",
            $ink[0],
            $ink[1],
            $ink[2],
            $bold ? 'FB' : 'FR',
            $size * $this->scale,
            $x * $this->scale,
            self::PAGE_HEIGHT - $y * $this->scale,
            $this->escape($text)
        );
    }

    public function block(array $lines, int $x, int $y, int $width, int $size, int $leading, array $options = []): int
    {
        $bold = (bool) ($options['bold'] ?? false);
        $limit = $options['lines'] ?? null;

        if ($limit !== null && $limit < 1) {
            return 0;
        }

        $wrapped = [];

        foreach ($lines as $line) {
            foreach ($this->wrap($this->flatten($line), $size, $bold, $width) as $part) {
                $wrapped[] = $part;
            }
        }

        if ($limit !== null && count($wrapped) > $limit) {
            $wrapped = array_slice($wrapped, 0, $limit);
            $last = count($wrapped) - 1;
            $wrapped[$last] = $this->fit($wrapped[$last] . '…', $size, $bold, $width);
        }

        foreach ($wrapped as $i => $line) {
            $this->text($line, $x, $y + $i * $leading, $size, $options);
        }

        return count($wrapped);
    }

    public function lineCount(string $text, int $size, int $width, bool $bold = false): int
    {
        return count($this->wrap($this->flatten($text), $size, $bold, $width));
    }

    public function widthOf(string $text, int $size, bool $bold = false): float
    {
        $bytes = $this->toWinAnsi($text);
        $total = 0;

        for ($i = 0, $n = strlen($bytes); $i < $n; $i++) {
            $total += self::WIDTHS[$bold][max(0, ord($bytes[$i]) - 32)] ?? 500;
        }

        return $total * $size / 1000;
    }

    public function render(): string
    {
        $pageCount = count($this->pages);
        $firstPage = self::FIXED_OBJECTS + 1;
        $firstContent = $firstPage + $pageCount;
        $firstFont = $firstContent + $pageCount;
        $firstImage = $firstFont + self::FONT_OBJECTS;

        $resources = '';
        $number = $firstImage;

        foreach ($this->images as $index => $image) {
            $resources .= sprintf('/Im%d %d 0 R ', $index, $number);
            $number += $image['mask'] === null ? 1 : 2;
        }

        $objects = [
            sprintf('<< /Type /Catalog /Pages %d 0 R >>', self::FIXED_OBJECTS),
            sprintf(
                '<< /Type /Pages /Kids [%s] /Count %d >>',
                implode(' ', array_map(static fn ($i) => ($firstPage + $i) . ' 0 R', range(0, $pageCount - 1))),
                $pageCount
            ),
        ];

        foreach (array_keys($this->pages) as $i) {
            $objects[] = sprintf(
                '<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /ProcSet [/PDF /Text /ImageC] /Font << /FR %d 0 R /FB %d 0 R >> /XObject << %s>> >> /Contents %d 0 R >>',
                self::FIXED_OBJECTS,
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $firstFont,
                $firstFont + 1,
                $resources,
                $firstContent + $i
            );
        }

        foreach ($this->pages as $stream) {
            $objects[] = $this->streamObject('/Filter /FlateDecode', (string) gzcompress($stream, 6));
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $number = $firstImage;

        foreach ($this->images as $image) {
            $mask = $image['mask'] === null ? '' : sprintf(' /SMask %d 0 R', $number + 1);
            $objects[] = $this->streamObject(
                sprintf(
                    '/Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode%s',
                    $image['width'],
                    $image['height'],
                    $mask
                ),
                $image['data']
            );
            $number++;

            if ($image['mask'] !== null) {
                $objects[] = $this->streamObject(
                    sprintf(
                        '/Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode',
                        $image['width'],
                        $image['height']
                    ),
                    $image['mask']
                );
                $number++;
            }
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $i => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $i + 1, $body);
        }

        $start = strlen($pdf);
        $pdf .= sprintf("xref\n0 %d\n0000000000 65535 f \n", count($objects) + 1);

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf . sprintf("trailer\n<< /Size %d /Root 1 0 R >>\nstartxref\n%d\n%%%%EOF\n", count($objects) + 1, $start);
    }

    private function place(?array $image, int $x, int $y, int $width, int $height): void
    {
        if ($image === null) {
            return;
        }

        $index = count($this->images);
        $this->images[$index] = $image;

        $this->pages[$this->current] .= sprintf(
            "q %.2f 0 0 %.2f %.2f %.2f cm /Im%d Do Q\n",
            $width * $this->scale,
            $height * $this->scale,
            $x * $this->scale,
            self::PAGE_HEIGHT - ($y + $height) * $this->scale,
            $index
        );
    }

    private function rasterize(string $path, ?float $ratio = null, float $verticalFocus = 0.5, bool $keepAlpha = false): ?array
    {
        if (!is_readable($path)) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));

        if ($source === false) {
            return null;
        }

        if (!imageistruecolor($source)) {
            imagepalettetotruecolor($source);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $cropX = 0;
        $cropY = 0;

        if ($ratio !== null) {
            if ($width / $height > $ratio) {
                $cropped = (int) round($height * $ratio);
                $cropX = (int) round(($width - $cropped) / 2);
                $width = $cropped;
            } else {
                $cropped = (int) round($width / $ratio);
                $cropY = (int) round(($height - $cropped) * min(1, max(0, $verticalFocus)));
                $height = $cropped;
            }
        }

        $mask = $keepAlpha ? $this->alphaOf($source, $cropX, $cropY, $width, $height) : null;

        $flat = imagecreatetruecolor($width, $height);
        imagealphablending($flat, false);

        if ($keepAlpha) {
            imagecopy($flat, $source, 0, 0, $cropX, $cropY, $width, $height);
        } else {
            imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
            imagealphablending($flat, true);
            imagecopy($flat, $source, 0, 0, $cropX, $cropY, $width, $height);
        }

        imagedestroy($source);
        imagesavealpha($flat, false);

        ob_start();
        imagejpeg($flat, null, self::JPEG_QUALITY);
        $data = (string) ob_get_clean();
        imagedestroy($flat);

        return ['data' => $data, 'mask' => $mask, 'width' => $width, 'height' => $height];
    }

    private function alphaOf($source, int $cropX, int $cropY, int $width, int $height): string
    {
        $levels = [];

        for ($a = 0; $a <= 127; $a++) {
            $levels[$a] = chr(255 - (int) round($a * 255 / 127));
        }

        $mask = '';

        for ($y = 0; $y < $height; $y++) {
            $row = '';

            for ($x = 0; $x < $width; $x++) {
                $row .= $levels[(imagecolorat($source, $cropX + $x, $cropY + $y) >> 24) & 0x7F];
            }

            $mask .= $row;
        }

        return (string) gzcompress($mask, 6);
    }

    private function streamObject(string $dictionary, string $data): string
    {
        return sprintf("<< %s /Length %d >>\nstream\n%s\nendstream", $dictionary, strlen($data), $data);
    }

    private function fit(string $text, int $size, bool $bold, int $width): string
    {
        if ($this->widthOf($text, $size, $bold) <= $width) {
            return $text;
        }

        while ($text !== '' && $this->widthOf($text . '…', $size, $bold) > $width) {
            $text = mb_substr($text, 0, mb_strlen($text) - 1);
        }

        return rtrim($text) . '…';
    }

    private function wrap(string $text, int $size, bool $bold, int $width): array
    {
        $lines = [];

        foreach (preg_split('/\R/', trim($text)) ?: [] as $paragraph) {
            $current = '';

            foreach (preg_split('/\s+/', trim($paragraph)) ?: [] as $word) {
                if ($word === '') {
                    continue;
                }

                $candidate = $current === '' ? $word : $current . ' ' . $word;

                if ($this->widthOf($candidate, $size, $bold) <= $width) {
                    $current = $candidate;
                    continue;
                }

                if ($current !== '') {
                    $lines[] = $current;
                }

                $current = $this->widthOf($word, $size, $bold) > $width ? $this->fit($word, $size, $bold, $width) : $word;
            }

            if ($current !== '') {
                $lines[] = $current;
            }
        }

        return $lines;
    }

    private function flatten(?string $text): string
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", (string) $text);
        $text = preg_replace('/<\/(p|li|div|h[1-6])>/i', "\n", (string) $text);
        $text = preg_replace('/<[^>]*>/', ' ', (string) $text);
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? '';
        $text = preg_replace('/ ([,.\)\]])/', '$1', $text) ?? '';

        return trim(preg_replace('/([\(\[]) /', '$1', $text) ?? '');
    }

    private function toWinAnsi(string $text): string
    {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);

        return $converted === false ? preg_replace('/[^\x20-\x7e]/', '', $text) ?? '' : $converted;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], $this->toWinAnsi($text));
    }
}
