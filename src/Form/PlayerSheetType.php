<?php

namespace App\Form;

use App\Entity\Access;
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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Regex;

class PlayerSheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sheet = $builder->getData();
        $spellCategories = $sheet?->getPersonnage()?->getEcole()?->hasTattoos()
            ? ['MAGIE', 'KIHO', 'TATOUAGE']
            : ['MAGIE', 'KIHO'];
        $equipped = array_filter([$sheet?->getArmeActuelle()?->getId()]) ?: [0];

        $objectQuery = function (EntityRepository $er, ?string $category) use ($options, $equipped) {
            $qb = $er->createQueryBuilder('o')->orderBy('o.nom', 'ASC');

            if ($category !== null) {
                $qb->andWhere('o.categorie = :cat')->setParameter('cat', $category);
            }

            if (!$options['sees_locked']) {
                $qb->andWhere('(o.access = :access AND (o.publishedAt IS NULL OR o.publishedAt <= :now)) OR o.id IN (:allowed)')
                    ->setParameter('access', Access::PUBLIC)
                    ->setParameter('now', new \DateTime())
                    ->setParameter('allowed', array_merge($equipped, $options['unlocked']));
            }

            return $qb;
        };

        $advantageOptions = [
            'class' => Avantage::class,
            'choice_label' => 'nom',
            'placeholder' => '— Aucun —',
            'required' => false,
            'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                ->where('a.genre = :genre')->setParameter('genre', 'Avantage')->orderBy('a.nom', 'ASC'),
        ];
        $disadvantageOptions = [
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
            ->add('avantage1', EntityType::class, $advantageOptions)
            ->add('avantage2', EntityType::class, $advantageOptions)
            ->add('desavantage1', EntityType::class, $disadvantageOptions)
            ->add('desavantage2', EntityType::class, $disadvantageOptions)
            ->add('compCombatActuelle', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('c')
                    ->where('c.categorie = :cat')->setParameter('cat', 'Bugei')->orderBy('c.nom', 'ASC'),
            ])
            ->add('armure', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $objectQuery($er, 'ARMURE'),
            ])
            ->add('armeActuelle', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $objectQuery($er, null),
            ])
            ->add('honneur', NumberType::class, ['required' => false, 'scale' => 1])
            ->add('gloire', NumberType::class, ['required' => false, 'scale' => 1])
            ->add('infamie', NumberType::class, ['required' => false, 'scale' => 1])
            ->add('statut', NumberType::class, ['required' => false, 'scale' => 1])
            ->add('age', IntegerType::class, ['required' => false])
            ->add('height', TextType::class, ['required' => false])
            ->add('weight', TextType::class, ['required' => false])
            ->add('hair', TextType::class, ['required' => false])
            ->add('eyes', TextType::class, ['required' => false])
            ->add('appearance', TextareaType::class, ['required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('koku', IntegerType::class, ['required' => false])
            ->add('bu', IntegerType::class, ['required' => false])
            ->add('zeni', IntegerType::class, ['required' => false])
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
            ->add('inventoryItems', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('o')->orderBy('o.nom', 'ASC'),
            ])
            ->add('knownSpells', EntityType::class, [
                'class' => Sort::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('s')
                    ->where('s.categorie IN (:categories)')->setParameter('categories', $spellCategories)
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

        $skillOptions = [
            'class' => Competence::class,
            'choice_label' => 'nom',
            'group_by' => 'categorie',
            'placeholder' => '— Aucune —',
            'required' => false,
        ];
        $valueOptions = [
            'placeholder' => '—',
            'required' => false,
            'choices' => array_combine(range(1, 10), range(1, 10)),
        ];

        for ($i = 1; $i <= 20; $i++) {
            $builder
                ->add('competence' . $i, EntityType::class, $skillOptions)
                ->add('valeur' . $i, ChoiceType::class, $valueOptions)
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
            'unlocked' => [],
        ]);
    }
}
