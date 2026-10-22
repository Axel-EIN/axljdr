<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MapCoordinateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'scale' => 2,
            'required' => false,
            'attr' => ['min' => 1, 'max' => 99, 'step' => '.05'],
        ]);
    }

    public function getParent(): string
    {
        return NumberType::class;
    }
}
