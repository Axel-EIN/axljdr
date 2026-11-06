<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Regex;

class StrongPassword extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Regex([
                'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,32}$/',
                'message' => 'Le mot de passe doit contenir 8 à 32 caractères, avec au moins un chiffre, une minuscule et une majuscule.',
            ]),
        ];
    }
}
