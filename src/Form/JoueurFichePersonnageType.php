<?php

namespace App\Form;

use App\Entity\Avantage;
use App\Entity\Competence;
use App\Entity\FichePersonnage;
use App\Entity\Objet;
use App\Entity\Sort;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Regex;

class JoueurFichePersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fiche = $builder->getData();
        // Une arme verrouillée déjà équipée reste proposée, sinon l'enregistrement l'effacerait.
        $equipped = array_filter([
            $fiche?->getArme()?->getId(),
            $fiche?->getArme2()?->getId(),
            $fiche?->getArmeActuelle()?->getId(),
        ]) ?: [0];

        $armes = function (EntityRepository $er, ?string $categorie) use ($options, $equipped) {
            $qb = $er->createQueryBuilder('o')->orderBy('o.nom', 'ASC');

            if ($categorie !== null) {
                $qb->andWhere('o.categorie = :cat')->setParameter('cat', $categorie);
            }

            if (!$options['sees_locked']) {
                $qb->andWhere('o.locked = false OR o.id IN (:equipped)')->setParameter('equipped', $equipped);
            }

            return $qb;
        };

        $avantageOptions = [
            'class' => Avantage::class,
            'choice_label' => 'nom',
            'placeholder' => '— Aucun —',
            'required' => false,
            'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                ->where('a.genre = :genre')->setParameter('genre', 'Avantage')->orderBy('a.nom', 'ASC'),
        ];
        $desavantageOptions = [
            'class' => Avantage::class,
            'choice_label' => 'nom',
            'placeholder' => '— Aucun —',
            'required' => false,
            'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                ->where('a.genre = :genre')->setParameter('genre', 'Désavantage')->orderBy('a.nom', 'ASC'),
        ];

        $builder
            ->add('constitution', IntegerType::class)
            ->add('volonte', IntegerType::class)
            ->add('reflexes', IntegerType::class)
            ->add('intuition', IntegerType::class)
            ->add('agilite', IntegerType::class)
            ->add('intelligence', IntegerType::class)
            ->add('forceStat', IntegerType::class)
            ->add('perception', IntegerType::class)
            ->add('vide', IntegerType::class)
            ->add('avantage1', EntityType::class, $avantageOptions)
            ->add('avantage2', EntityType::class, $avantageOptions)
            ->add('desavantage1', EntityType::class, $desavantageOptions)
            ->add('desavantage2', EntityType::class, $desavantageOptions)
            ->add('compCombatActuelle', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('c')
                    ->where('c.categorie = :cat')->setParameter('cat', 'Bugei')->orderBy('c.nom', 'ASC'),
            ])
            ->add('arme2', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $armes($er, 'ARME'),
            ])
            ->add('armeActuelle', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $armes($er, null),
            ])
            ->add('initiativeModifier', IntegerType::class, ['required' => false])
            ->add('ndModifier', IntegerType::class, ['required' => false])
            ->add('reductionModifier', IntegerType::class, ['required' => false])
            ->add('attaqueModifier', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d+g\d+$/',
                        'message' => 'Le format doit être XgY (ex: 1g0, 2g1).',
                    ]),
                ],
            ])
            ->add('knownSpells', EntityType::class, [
                'class' => Sort::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('s')
                    ->where('s.categorie = :categorie')->setParameter('categorie', 'MAGIE')
                    ->orderBy('s.nom', 'ASC'),
            ])
            ->add('dmgModifier', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d+g\d+$/',
                        'message' => 'Le format doit être XgY (ex: 1g0, 2g1).',
                    ]),
                ],
            ])
        ;

        $competenceOptions = [
            'class' => Competence::class,
            'choice_label' => 'nom',
            'group_by' => 'categorie',
            'placeholder' => '— Aucune —',
            'required' => false,
        ];
        $valeurOptions = [
            'placeholder' => '—',
            'required' => false,
            'choices' => array_combine(range(1, 10), range(1, 10)),
        ];

        for ($i = 1; $i <= 20; $i++) {
            $builder
                ->add('competence' . $i, EntityType::class, $competenceOptions)
                ->add('valeur' . $i, ChoiceType::class, $valeurOptions)
                ->add('specialisations' . $i, HiddenType::class, ['required' => false])
                ->add('compEcole' . $i, HiddenType::class, ['required' => false])
                ->add('speEcole' . $i, HiddenType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FichePersonnage::class,
            'sees_locked' => false,
        ]);
    }
}
