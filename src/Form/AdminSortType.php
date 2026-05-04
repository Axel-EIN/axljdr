<?php

namespace App\Form;

use App\Entity\Sort;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'constraints' => [ new Length( [ 'max' => 60 ] ) ] ])
        ->add('originalName', TextType::class, [
            'required' => false,
            'constraints' => [ new Length( [ 'max' => 60 ] ) ] ])
        ->add('categorie', ChoiceType::class, [
            'choices'  => [
                'MAGIE' => 'MAGIE',
                'MAHO' => 'MAHO',
                "KIHO" => "KIHO",
                'TATOUAGE' => 'TATOUAGE',
            ],
        ])
        ->add('anneau', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'AIR' => 'AIR',
                'EAU' => 'EAU',
                "FEU" => "FEU",
                'TERRE' => 'TERRE',
                'VIDE' => 'VIDE',
                'UNIVERSEL' => 'UNIVERSEL',
            ],
        ])
        ->add('niveau', IntegerType::class, [
            'required' => false ] )
        ->add('numero', IntegerType::class)
        ->add('portee', TextType::class, [
            'required' => false ] )
        ->add('zone', TextType::class, [
            'required' => false ] )
        ->add('duree', TextType::class, [
            'required' => false ] )
        ->add('augmentations', TextType::class, [
            'required' => false ] )
        ->add('description', TextareaType::class, [ 
            'constraints' => [ new Length( [ 'max' => 3000 ] ) ] ])
        ->add('keyword1', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'Affliction' => 'Affliction',
                'Art de la Guerre' => 'Guerre',
                'Artisanat' => 'Artisanat',
                'Bénédiction' => 'Bénédiction',
                'Défense' => 'Défense',
                'Detection' => 'Detection',
                'Divination' => 'Divination',
                'Explosion' => 'Explosion',
                'Glyphe' => 'Glyphe',
                'Illusion' => 'Illusion',
                'Invocation' => 'Invocation',
                'Jade' => 'Jade',
                'Soins' => 'Soins',
                'Souillure' => 'Souillure',
                'Tonnerre' => 'Tonnerre',
                'Voyage' => 'Voyage',
                'Atemi' => 'Atemi',
            ],
        ])
        ->add('keyword2', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'Affliction' => 'Affliction',
                'Art de la Guerre' => 'Guerre',
                'Artisanat' => 'Artisanat',
                'Bénédiction' => 'Bénédiction',
                'Défense' => 'Défense',
                'Detection' => 'Detection',
                'Divination' => 'Divination',
                'Explosion' => 'Explosion',
                'Glyphe' => 'Glyphe',
                'Illusion' => 'Illusion',
                'Invocation' => 'Invocation',
                'Jade' => 'Jade',
                'Soins' => 'Soins',
                'Souillure' => 'Souillure',
                'Tonnerre' => 'Tonnerre',
                'Voyage' => 'Voyage',
                'Atemi' => 'Atemi',
            ],
        ])
        ->add('keyword3', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'Affliction' => 'Affliction',
                'Art de la Guerre' => 'Guerre',
                'Artisanat' => 'Artisanat',
                'Bénédiction' => 'Bénédiction',
                'Défense' => 'Défense',
                'Detection' => 'Detection',
                'Divination' => 'Divination',
                'Explosion' => 'Explosion',
                'Glyphe' => 'Glyphe',
                'Illusion' => 'Illusion',
                'Invocation' => 'Invocation',
                'Jade' => 'Jade',
                'Soins' => 'Soins',
                'Souillure' => 'Souillure',
                'Tonnerre' => 'Tonnerre',
                'Voyage' => 'Voyage',
                'Atemi' => 'Atemi',
            ],
        ])
        ->add('kihoType', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'Intérieur' => 'Intérieur',
                'Mystique' => 'Mystique',
                'Karmique' => 'Karmique',
                'Martial' => 'Martial',
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sort::class,
        ]);
    }
}
