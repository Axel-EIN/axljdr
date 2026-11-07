<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\File;

class VideoFile extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new File([
                'maxSize' => '50M',
                'mimeTypes' => ['video/mp4', 'video/webm'],
                'mimeTypesMessage' => 'La vidéo doit être au format MP4 ou WebM.',
                'maxSizeMessage' => 'La vidéo ne doit pas dépasser {{ limit }} {{ suffix }}.',
            ]),
        ];
    }
}
