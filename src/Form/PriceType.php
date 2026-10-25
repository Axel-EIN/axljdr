<?php

namespace App\Form;

use App\Entity\Monnaie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (['koku' => 'Koku', 'bu' => 'Bu', 'zeni' => 'Zeni'] as $coin => $label) {
            $builder->add($coin, IntegerType::class, [
                'label' => $label,
                'required' => false,
                'attr' => ['min' => 0],
            ]);
        }

        $builder->addModelTransformer(new CallbackTransformer(
            function (?int $price): array {
                if ($price === null) {
                    return ['koku' => null, 'bu' => null, 'zeni' => null];
                }

                return [
                    'koku' => intdiv($price, Monnaie::ZENI_PAR_KOKU),
                    'bu' => intdiv($price % Monnaie::ZENI_PAR_KOKU, Monnaie::ZENI_PAR_BU),
                    'zeni' => $price % Monnaie::ZENI_PAR_BU,
                ];
            },
            function (?array $coins): ?int {
                if ($coins === null || array_filter($coins, fn ($value) => $value !== null) === []) {
                    return null;
                }

                return (int) $coins['koku'] * Monnaie::ZENI_PAR_KOKU
                    + (int) $coins['bu'] * Monnaie::ZENI_PAR_BU
                    + (int) $coins['zeni'];
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
