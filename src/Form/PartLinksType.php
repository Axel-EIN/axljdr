<?php

namespace App\Form;

use App\Service\PartLinker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartLinksType extends AbstractType
{
    private $partLinker;

    public function __construct(PartLinker $partLinker)
    {
        $this->partLinker = $partLinker;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->partLinker->choices(),
            'multiple' => true,
            'required' => false,
            'label' => 'Liens',
            'attr' => ['size' => 10],
        ]);
        $resolver->setRequired('part');
        $resolver->setAllowedTypes('part', 'int');
        $resolver->setNormalizer('getter', function (Options $options) {
            $part = $options['part'];

            return fn ($record) => $record->getPartLinks($part);
        });
        $resolver->setNormalizer('setter', function (Options $options) {
            $part = $options['part'];

            return fn ($record, ?array $links) => $record->setPartLinks($part, $links);
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
