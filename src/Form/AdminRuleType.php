<?php

namespace App\Form;

use App\Entity\Rule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Length;

class AdminRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('numero', IntegerType::class)
            ->add('base', CheckboxType::class, ['required' => false])
            ->add('listEntity', ChoiceType::class, [ 'label' => 'Bibliothèque', 
                'choices'  => [
                    'Avantages / Désavantages' => 'avantage',
                    'Compétences' => 'competence',
                    'Sorts' => 'sort',
                    'Kihos' => 'kiho',
                    'Tatoos' => 'tatoo',
                ],
            ])
            ->add('listTabField', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('listFilterField', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('pdf', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('listIntro', TextareaType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 3000 ] ) ] ] )
            ->add('part1titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part1', TextareaType::class, ['required' => false])
            ->add('part1aside', textareaType::class, ['required' => false])
            ->add('part2titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part2', TextareaType::class, ['required' => false])
            ->add('part2aside', textareaType::class, ['required' => false])
            ->add('part3titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part3', TextareaType::class, ['required' => false])
            ->add('part3aside', textareaType::class, ['required' => false])
            ->add('part4titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part4', TextareaType::class, ['required' => false])
            ->add('part4aside', textareaType::class, ['required' => false])
            ->add('part5titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part5', TextareaType::class, ['required' => false])
            ->add('part5aside', textareaType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rule::class,
        ]);
    }
}
