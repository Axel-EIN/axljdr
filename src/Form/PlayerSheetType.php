<?php

namespace App\Form;

use App\Entity\Avantage;
use App\Entity\Competence;
use App\Entity\FichePersonnage;
use App\Entity\Objet;
use App\Entity\Sort;
use App\Service\Visibility;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class PlayerSheetType extends AbstractType
{
    public const TEXT_MAX_LENGTH = 20000;

    private const BITMASK = ['pattern' => '/^[01]{0,6}$/', 'message' => 'Valeur de spécialisation invalide.'];

    private Visibility $visibility;
    private array $readableIds = [];

    public function __construct(Visibility $visibility)
    {
        $this->visibility = $visibility;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sheet = $builder->getData();
        $spellCategories = $sheet?->getPersonnage()?->getEcole()?->hasTattoos()
            ? ['MAGIE', 'KIHO', 'TATOUAGE']
            : ['MAGIE', 'KIHO'];

        $objectQuery = function (EntityRepository $er, ?string $category) {
            $qb = $this->readable($er, 'o');

            if ($category !== null) {
                $qb->andWhere('o.categorie = :cat')->setParameter('cat', $category);
            }

            return $qb;
        };

        $advantageOptions = [
            'class' => Avantage::class,
            'choice_label' => 'nom',
            'placeholder' => '— Aucun —',
            'required' => false,
            'query_builder' => fn(EntityRepository $er) => $this->readable($er, 'a')
                ->andWhere('a.genre = :genre')->setParameter('genre', 'Avantage'),
        ];
        $disadvantageOptions = [
            'class' => Avantage::class,
            'choice_label' => 'nom',
            'placeholder' => '— Aucun —',
            'required' => false,
            'query_builder' => fn(EntityRepository $er) => $this->readable($er, 'a')
                ->andWhere('a.genre = :genre')->setParameter('genre', 'Désavantage'),
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
                'query_builder' => fn(EntityRepository $er) => $this->readable($er, 'c')
                    ->andWhere('c.categorie = :cat')->setParameter('cat', 'Bugei'),
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
            ->add('appearance', TextareaType::class, [
                'required' => false,
                'constraints' => [new Length(['max' => self::TEXT_MAX_LENGTH])],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'constraints' => [new Length(['max' => self::TEXT_MAX_LENGTH])],
            ])
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
                'query_builder' => fn(EntityRepository $er) => $objectQuery($er, null),
            ])
            ->add('knownSpells', EntityType::class, [
                'class' => Sort::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $this->readable($er, 's')
                    ->andWhere('s.categorie IN (:categories)')->setParameter('categories', $spellCategories),
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
            'query_builder' => fn(EntityRepository $er) => $this->readable($er, 'c'),
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
                ->add('specialisations' . $i, HiddenType::class, [
                    'required' => false,
                    'constraints' => [new Regex(self::BITMASK)],
                ])
                ->add('compEcole' . $i, HiddenType::class, [
                    'required' => false,
                    'constraints' => [new Range(['min' => 0, 'max' => 10])],
                ])
                ->add('speEcole' . $i, HiddenType::class, [
                    'required' => false,
                    'constraints' => [new Regex(self::BITMASK)],
                ]);
        }
    }

    private function readable(EntityRepository $er, string $alias): QueryBuilder
    {
        $class = $er->getClassName();

        if (!isset($this->readableIds[$class])) {
            $this->readableIds[$class] = [];

            foreach ($er->findAll() as $element) {
                if ($this->visibility->isReadable($element)) {
                    $this->readableIds[$class][] = $element->getId();
                }
            }
        }

        return $er->createQueryBuilder($alias)
            ->andWhere($alias . '.id IN (:readable)')
            ->setParameter('readable', $this->readableIds[$class] ?: [0])
            ->orderBy($alias . '.nom', 'ASC');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FichePersonnage::class,
        ]);
    }
}
