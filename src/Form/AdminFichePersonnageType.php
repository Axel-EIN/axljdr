<?php

namespace App\Form;

use App\Entity\Avantage;
use App\Entity\Competence;
use App\Entity\Objet;
use App\Entity\Personnage;
use App\Entity\FichePersonnage;
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
use Symfony\Component\Validator\Constraints\Regex;

class AdminFichePersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('personnage', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'prenom',
                'required' => true
            ])
            ->add('creationExp', IntegerType::class)
            ->add('avantage1', EntityType::class, [
                'class' => Avantage::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucun —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                    ->where('a.genre = :genre')->setParameter('genre', 'Avantage')->orderBy('a.nom', 'ASC'),
            ])
            ->add('avantage2', EntityType::class, [
                'class' => Avantage::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucun —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                    ->where('a.genre = :genre')->setParameter('genre', 'Avantage')->orderBy('a.nom', 'ASC'),
            ])
            ->add('desavantage1', EntityType::class, [
                'class' => Avantage::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucun —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                    ->where('a.genre = :genre')->setParameter('genre', 'Désavantage')->orderBy('a.nom', 'ASC'),
            ])
            ->add('desavantage2', EntityType::class, [
                'class' => Avantage::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucun —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                    ->where('a.genre = :genre')->setParameter('genre', 'Désavantage')->orderBy('a.nom', 'ASC'),
            ])
            ->add('arme', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('o')
                    ->where('o.categorie = :cat')->setParameter('cat', 'ARME')->orderBy('o.nom', 'ASC'),
            ])
            ->add('arme2', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('o')
                    ->where('o.categorie = :cat')->setParameter('cat', 'ARME')->orderBy('o.nom', 'ASC'),
            ])
            ->add('armure', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('o')
                    ->where('o.categorie = :cat')->setParameter('cat', 'ARMURE')->orderBy('o.nom', 'ASC'),
            ])
            ->add('reductionModifier', IntegerType::class, ['required' => false, 'label' => 'Modificateur de réduction'])
            ->add('ndModifier', IntegerType::class, ['required' => false, 'label' => 'Modificateur de ND pour être touché'])
            ->add('dmgModifier', TextType::class, [
                'required' => false,
                'label'    => 'Modificateur de dommages (XgY)',
                'attr'     => ['placeholder' => 'ex: 1g0'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d+g\d+$/',
                        'message' => 'Le format doit être XgY (ex: 1g0, 2g1).',
                    ]),
                ],
            ])
            ->add('initiativeModifier', IntegerType::class, [
                'required' => false,
                'label'    => 'Modificateur d\'initiative',
            ])
            ->add('attaqueModifier', TextType::class, [
                'required' => false,
                'label'    => 'Modificateur d\'attaque (XgY)',
                'attr'     => ['placeholder' => 'ex: 1g0'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d+g\d+$/',
                        'message' => 'Le format doit être XgY (ex: 1g0, 2g1).',
                    ]),
                ],
            ])
            ->add('honneur', NumberType::class, ['required' => false, 'scale' => 1, 'attr' => ['min' => 0, 'max' => 10, 'step' => 0.1]])
            ->add('gloire', NumberType::class, ['required' => false, 'scale' => 1, 'attr' => ['min' => 0, 'max' => 10, 'step' => 0.1]])
            ->add('infamie', NumberType::class, ['required' => false, 'scale' => 1, 'attr' => ['min' => 0, 'max' => 10, 'step' => 0.1]])
            ->add('souillure', NumberType::class, ['required' => false, 'scale' => 1, 'attr' => ['min' => 0, 'max' => 10, 'step' => 0.1]])
            ->add('constitution', IntegerType::class)
            ->add('volonte', IntegerType::class)
            ->add('reflexes', IntegerType::class)
            ->add('intuition', IntegerType::class)
            ->add('agilite', IntegerType::class)
            ->add('intelligence', IntegerType::class)
            ->add('forceStat', IntegerType::class)
            ->add('perception', IntegerType::class)
            ->add('vide', IntegerType::class)
            ->add('knownSpells', EntityType::class, [
                'class' => Sort::class,
                'label' => 'Sorts connus (Ctrl + clic pour en sélectionner plusieurs)',
                'choice_label' => fn(Sort $sort) => $sort->getNom() . ' (' . $sort->getNiveau() . ')',
                'group_by' => 'anneau',
                'multiple' => true,
                'required' => false,
                'attr' => ['size' => 20],
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('s')
                    ->where('s.categorie = :categorie')->setParameter('categorie', 'MAGIE')
                    ->orderBy('s.anneau', 'ASC')->addOrderBy('s.niveau', 'ASC')->addOrderBy('s.nom', 'ASC'),
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

        $compEcoleOptions = [
            'required' => false,
            'placeholder' => false,
            'choices' => ['Non' => 0, 'Offerte (1pt)' => 1, 'Offerte (2pts)' => 2],
            'label' => 'École',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $builder
                ->add('competence' . $i, EntityType::class, $competenceOptions)
                ->add('valeur' . $i, ChoiceType::class, $valeurOptions)
                ->add('specialisations' . $i, HiddenType::class, ['required' => false])
                ->add('compEcole' . $i, ChoiceType::class, $compEcoleOptions)
                ->add('speEcole' . $i, HiddenType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FichePersonnage::class,
        ]);
    }
}
