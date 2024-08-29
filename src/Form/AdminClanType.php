<?php

namespace App\Form;

use App\Entity\Clan;
use App\Entity\Personnage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Validator\Constraints\Length;

class AdminClanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('genre', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 1 ] ) ] ] )
            ->add('estMajeur', CheckboxType::class, ['required' => false])
            ->add('citation', TextType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('description', TextareaType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 600 ] ) ] ] )
            ->add('longDescription', TextareaType::class, ['required' => false] )
            ->add('couleur', ColorType::class, ['required' => false] )
            ->add('mon', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('video', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('territoireCarte', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('territoireDesc', TextareaType::class, ['required' => false])
            ->add('chef', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'prenom',
                'group_by' => 'clan.nom',
                'placeholder' => 'Non défini',
                'required' => false
            ])
        ;

        $builder
            ->get('estMajeur')
            ->addModelTransformer(new CallbackTransformer(
                function ($activeAsString) {
                    // transform the string to boolean
                    return (bool)(int)$activeAsString;
                },
                function ($activeAsBoolean) {
                    // transform the boolean to string
                    return (string)(int)$activeAsBoolean;
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clan::class,
        ]);
    }
}
