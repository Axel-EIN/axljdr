<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Image;

class ImageFile extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Image([
                'maxSize' => '5M',
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'mimeTypesMessage' => 'L\'image doit être au format JPG, PNG, GIF ou WebP.',
                'maxSizeMessage' => 'L\'image ne doit pas dépasser {{ limit }} {{ suffix }}.',
                'maxWidth' => 6000,
                'maxHeight' => 6000,
                'maxPixels' => 16000000,
                'maxWidthMessage' => 'L\'image ne doit pas dépasser {{ max_width }} pixels de large.',
                'maxHeightMessage' => 'L\'image ne doit pas dépasser {{ max_height }} pixels de haut.',
                'maxPixelsMessage' => 'L\'image ne doit pas dépasser {{ max_pixels }} pixels au total (par exemple 4000 × 4000).',
            ]),
        ];
    }
}
