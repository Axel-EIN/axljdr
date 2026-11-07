<?php

namespace App\Service;

class RichText
{
    private const HEADINGS = [
        1 => 'heading-articlepart',
        2 => 'heading-articlepart level-2',
        3 => 'heading-articlepart level-3',
    ];

    public function render(?string $text): string
    {
        $text = preg_replace_callback(
            '/^[ \t]*(#{1,3})[ \t]+(.+?)[ \t\r]*$/mu',
            fn (array $heading) => '<span class="' . self::HEADINGS[strlen($heading[1])] . '">' . $heading[2] . '</span>',
            (string) $text
        );

        return nl2br(preg_replace(
            [
                '/\*\*\*(?=\S)(.+?)(?<=\S)\*\*\*/u',
                '/\*\*(?=\S)(.+?)(?<=\S)\*\*/u',
                '/(?<![\w*])\*(?=[^\s*])(.+?)(?<=[^\s*])\*(?![\w*])/u',
                '/==(?=\S)(.+?)(?<=\S)==/u',
            ],
            ['<strong><em>$1</em></strong>', '<strong>$1</strong>', '<em>$1</em>', '<mark>$1</mark>'],
            $text
        ));
    }
}
