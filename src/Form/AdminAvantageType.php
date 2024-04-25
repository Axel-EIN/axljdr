<?php

namespace App\Form;

use App\Entity\Avantage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Entity\Clan;
use App\Entity\Classe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AdminAvantageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, array( 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ) )
        ->add('desavantage', CheckboxType::class, ['required' => false])
        ->add('type', ChoiceType::class, [
            'choices'  => [
                'MATERIEL' => 'MATERIEL',
                'MENTAL' => 'MENTAL',
                'MYSTIQUE' => 'MYSTIQUE',
                'PHYSIQUE' => 'PHYSIQUE',
                'SOCIAL' => 'SOCIAL',
                'SPIRITUEL' => 'SPIRITUEL'
            ],
        ])
        ->add('cout', IntegerType::class, array('label' => 'Coût (Avantage) / Gain (Désavantage)') )
        ->add('description', TextareaType::class, array( 'constraints' => [ new Length( [ 'max' => 3000 ] ) ] ))
        ->add('exclusive', EntityType::class, [
            'class' => Classe::class,
            'choice_label' => 'nom',
            'placeholder' => '',
            'required' => false
            ])
        ->add('discount', IntegerType::class, ['required' => false])
        ->add('discountClan', EntityType::class, [
            'class' => Clan::class,
            'choice_label' => 'nom',
            'placeholder' => '',
            'required' => false
            ])
        ->add('discountClan2', EntityType::class, [
            'class' => Clan::class,
            'choice_label' => 'nom',
            'placeholder' => '',
            'required' => false
            ])
        ->add('discountClasse', EntityType::class, [
            'class' => Classe::class,
            'choice_label' => 'nom',
            'placeholder' => '',
            'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avantage::class,
        ]);
    }
}