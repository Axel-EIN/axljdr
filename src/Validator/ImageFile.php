<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\File;

class ImageFile extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new File([
                'maxSize' => '5M',
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'mimeTypesMessage' => 'L\'image doit être au format JPG, PNG, GIF ou WebP.',
                'maxSizeMessage' => 'L\'image ne doit pas dépasser {{ limit }} {{ suffix }}.',
            ]),
        ];
    }
}
