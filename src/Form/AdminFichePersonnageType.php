<?php

namespace App\Form;

use App\Entity\Avantage;
use App\Entity\Competence;
use App\Entity\Objet;
use App\Entity\Personnage;
use App\Entity\FichePersonnage;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

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
            ->add('armure', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'placeholder' => '— Aucune —',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('o')
                    ->where('o.categorie = :cat')->setParameter('cat', 'ARMURE')->orderBy('o.nom', 'ASC'),
            ])
            ->add('reductionSpecial', IntegerType::class, ['required' => false, 'label' => 'Réduction spéciale'])
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
                ->add('valeur' . $i, ChoiceType::class, $valeurOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FichePersonnage::class,
        ]);
    }
}
