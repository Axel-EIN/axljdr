<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\File;

class PdfFile extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new File([
                'maxSize' => '10M',
                'mimeTypes' => ['application/pdf'],
                'mimeTypesMessage' => 'Le document doit être au format PDF.',
                'maxSizeMessage' => 'Le PDF ne doit pas dépasser {{ limit }} {{ suffix }}.',
            ]),
        ];
    }
}
