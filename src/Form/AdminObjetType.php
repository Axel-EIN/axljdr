<?php

namespace App\Form;

use App\Entity\Objet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdminObjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [ new Length( [ 'max' => 60 ] ) ]
            ])
            ->add('categorie', ChoiceType::class, [
                'choices'  => [
                    'ARME' => 'ARME',
                    'PROJECTILE' => 'PROJECTILE',
                    "ARMURE" => "ARMURE",
                    'OBJET' => 'OBJET',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'ÉPÉE OU SABRE' => 'ÉPÉE',
                    "ARME D'HAST" => "HAST",
                    'LANCE' => 'LANCE',
                    'ARME LOURDE' => 'LOURDE',
                    'ARME À CHAÎNE' => 'CHAÎNE',
                    'BÂTON' => 'BÂTON',
                    'COUTEAU' => 'COUTEAU',
                    'ÉVENTAIL DE GUERRE' => 'ÉVENTAIL',
                    'ARC' => 'ARC',
                    'ENSEMBLE' => 'ENSEMBLE',
                    'FLÈCHE' => 'FLÈCHE',
                    'ARME DE NINJUTSU' => 'NINJUTSU',
                    'DIVERS' => 'DIVERS',
                ],
            ])
            ->add('prix', MoneyType::class, [
                'required' => false,
                'currency' => 'JPY',
                'scale' => 0,
                'label' => 'Prix zeni ( 1 koku = 5 bu = 50 zeni )',
            ])
            ->add('numero', IntegerType::class, [
                'required' => false ] )
            ->add('image', FileType::class, [
                'mapped' => false,
                'data_class' => null,
                'required' => false
            ])
            ->add('description', TextareaType::class, [ 
                'constraints' => [ new Length( [ 'max' => 3000 ] ) ]
            ])
            ->add('regles', TextareaType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 3000 ] ) ]
            ])
            ->add('motCle1', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'Samurai' => 'Samurai',
                    'Moine' => 'Moine',
                    'Paysan' => 'Paysan',
                    'Ninja' => 'Ninja',
                ],
            ])
            ->add('motCle2', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'Samurai' => 'Samurai',
                    'Moine' => 'Moine',
                    'Paysan' => 'Paysan',
                    'Ninja' => 'Ninja',
                ],
            ])

            // ARMOR OBJECT
            ->add('ndArmure', IntegerType::class, [
                'required' => false ] )
            ->add('reduction', IntegerType::class, [
                'required' => false ] )

            // WEAPON OBJECT
            ->add('vd', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 60 ] ) ]
            ])
            ->add('taille', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'PETITE' => 'PETITE',
                    'GRANDE' => 'GRANDE',
                    'DOUBLE' => 'DOUBLE',
                ],
            ])
            ->add('poids', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'LÉGÈRE' => 'LÉGÈRE',
                    'LOURDE' => 'LOURDE',
                ],
            ])
            ->add('forceArc', IntegerType::class, [
                'required' => false ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objet::class,
        ]);
    }
}
