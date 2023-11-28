<?php

namespace App\Form;

use App\Entity\Competence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminCompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, array( 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('trait', ChoiceType::class, [
            'choices'  => [
                'AGILITÉ' => 'AGILITÉ',
                'CONSTITUTION' => 'CONSTITUTION',
                'FORCE' => 'FORCE',
                'INTELLIGENCE' => 'INTELLIGENCE',
                'INTUITION' => 'INTUITION',
                'PERCEPTION' => 'PERCEPTION',
                'RÉFLEXES' => 'RÉFLEXES',
                'VIDE' => 'VIDE',
                'VOLONTÉ' => 'VOLONTÉ',
                'VARIABLE' => 'VARIABLE',
            ],
        ])
        ->add('categorie', ChoiceType::class, [
            'choices'  => [
                'Compétence Noble' => 'Noble',
                'Compétence de Bugei' => 'Bugei',
                'Compétence de Marchand' => 'Marchand',
                'Compétence Dégradante' => 'Dégradante',
            ],
        ])
        ->add('globale', CheckboxType::class, ['required' => false])
        ->add('degradante', CheckboxType::class, ['required' => false])

        ->add('motCle1', ChoiceType::class, [ 'required' => false,
            'choices'  => [
                "Compétence d'Arme" => 'Arme',
                "Compétence d'Art" => 'Art',
                "Compétence d'Artisanat" => 'Artisanat',
                "Compétence Sociale" => 'Sociale',
                'Compétence de Spectacle' => 'Spectacle',
            ],
        ])
        ->add('motCle2', ChoiceType::class, [ 'required' => false,
            'choices'  => [
                "Compétence d'Arme" => 'Arme',
                "Compétence d'Art" => 'Art',
                "Compétence d'Artisanat" => 'Artisanat',
                "Compétence Sociale" => 'Sociale',
                'Compétence de Spectacle' => 'Spectacle',
            ],
        ])
        ->add('specialisation1', TextType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('specialisation2', TextType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('specialisation3', TextType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('specialisation4', TextType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('specialisation5', TextType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('specialisation6', TextType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ) )
        ->add('capacite', TextareaType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 3000 ] ) ] ))
        ->add('description', TextareaType::class, array( 'constraints' => [ new Length( [ 'max' => 3000 ] ) ] ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
}
