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
                'TERRE' => 'TERRE',
                'AIR' => 'AIR',
                "FEU" => "FEU",
                'EAU' => 'EAU',
                'VIDE' => 'VIDE',
                'UNIVERSEL' => 'UNIVERSEL',
            ],
        ])
        ->add('niveau', IntegerType::class, [
            'required' => false ] )
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
        ->add('motCle1', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'Illusion' => 'Illusion',
                'Tonnerre' => 'Tonnerre',
                'Défense' => 'Défense',
                'Voyage' => 'Voyage',
                'Divination' => 'Divination',
                'Arme' => 'Arme',
            ],
        ])
        ->add('motCle2', ChoiceType::class, [
            'required' => false,
            'choices'  => [
                'Artisanat' => 'Artisanat',
                'Art de la Guerre' => 'Art de la Guerre',
                'Soins' => 'Soins',
                'Glyphe' => 'Glyphe',
                'Jade' => 'Jade',
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
