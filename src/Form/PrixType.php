<?php

namespace App\Form;

use App\Entity\Monnaie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PrixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (['koku' => 'Koku', 'bu' => 'Bu', 'zeni' => 'Zeni'] as $piece => $label) {
            $builder->add($piece, IntegerType::class, [
                'label' => $label,
                'required' => false,
                'attr' => ['min' => 0],
            ]);
        }

        $builder->addModelTransformer(new CallbackTransformer(
            function (?int $prix): array {
                if ($prix === null) {
                    return ['koku' => null, 'bu' => null, 'zeni' => null];
                }

                return [
                    'koku' => intdiv($prix, Monnaie::ZENI_PAR_KOKU),
                    'bu' => intdiv($prix % Monnaie::ZENI_PAR_KOKU, Monnaie::ZENI_PAR_BU),
                    'zeni' => $prix % Monnaie::ZENI_PAR_BU,
                ];
            },
            function (?array $pieces): ?int {
                if ($pieces === null || array_filter($pieces, fn ($valeur) => $valeur !== null) === []) {
                    return null;
                }

                return (int) $pieces['koku'] * Monnaie::ZENI_PAR_KOKU
                    + (int) $pieces['bu'] * Monnaie::ZENI_PAR_BU
                    + (int) $pieces['zeni'];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
        ]);
    }
}
