<?php

namespace App\Form;

use App\Entity\Clan;
use App\Entity\Ecole;
use App\Entity\Classe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Validator\Constraints\Length;

class AdminEcoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 60 ] ) ] ] )
            ->add('image', FileType::class, [ 'required' => false, 'mapped' => false, 'data_class' => null ])
            ->add('description', TextareaType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 2000 ] ) ] ])
            ->add('bonus', TextType::class, [ 'required' => false , 'constraints' => [ new Length( [ 'max' => 20 ] ) ] ])
            ->add('competences', TextareaType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 600 ] ) ] ])
            ->add('equipements', TextareaType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 600 ] ) ] ])
            ->add('tech1Nom', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ])
            ->add('tech1Desc', TextareaType::class, ['required' => false] )
            ->add('tech2Nom', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ]])
            ->add('tech2Desc', TextareaType::class, ['required' => false] )
            ->add('tech3Nom', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ])
            ->add('tech3Desc', TextareaType::class, ['required' => false ] )
            ->add('tech4Nom', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ])
            ->add('tech4Desc', TextareaType::class, ['required' => false ] )
            ->add('tech5Nom', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ])
            ->add('tech5Desc', TextareaType::class, ['required' => false])
            ->add('techSpecialNom', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ])
            ->add('techSpecialDesc', TextareaType::class, ['required' => false])
            ->add('affinite', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 50 ] ) ] ])
            ->add('deficience', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 50 ] ) ] ])
            ->add('sorts', TextareaType::class, ['required' => false])
            ->add('classe', EntityType::class, [
                'class' => Classe::class,
                'choice_label' => 'nom'
            ])
            ->add('clan', EntityType::class, [
                'class' => Clan::class,
                'choice_label' => 'nom'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ecole::class,
        ]);
    }
}
