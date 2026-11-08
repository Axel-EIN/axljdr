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

        $text = preg_replace_callback(
            '/(?:^[ \t]*-[ \t]+.*(?:\R|$))+/mu',
            fn (array $list) => '<span class="text-list">' . implode('', array_map(
                fn (string $item) => '<span class="text-list-item">' . preg_replace('/^[ \t]*-[ \t]+/u', '', $item) . '</span>',
                preg_split('/\R/u', rtrim($list[0], "\r\n"))
            )) . '</span>',
            $text
        );

        $text = preg_replace_callback(
            '/\[([^\]\n]+)\]\(((?:https?:\/\/|\/)[^\s)"<>]*)\)/u',
            fn (array $link) => '<a href="' . $link[2] . '"' . (str_starts_with($link[2], '/') ? '' : ' target="_blank" rel="noopener"') . '>' . $link[1] . '</a>',
            $text
        );

        return nl2br(preg_replace(
            [
                '/\*\*\*(?=\S)(.+?)(?<=\S)\*\*\*/u',
                '/\*\*(?=\S)(.+?)(?<=\S)\*\*/u',
                '/(?<![\w*])\*(?=[^\s*])(.+?)(?<=[^\s*])\*(?![\w*])/u',
                '/==(?=\S)(.+?)(?<=\S)==/u',
            ],
            ['<span class="bold"><em>$1</em></span>', '<span class="bold">$1</span>', '<em>$1</em>', '<mark>$1</mark>'],
            $text
        ));
    }
}
