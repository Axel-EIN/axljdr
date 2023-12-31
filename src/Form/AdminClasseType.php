<?php

namespace App\Form;

use App\Entity\Classe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;

use Symfony\Component\Validator\Constraints\Length;

class AdminClasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('icone', FileType::class,
                [
                    'mapped' => false,
                    'data_class' => null,
                    'required' => false
                ]
            )
            ->add('image', FileType::class,
                [
                    'mapped' => false,
                    'data_class' => null,
                    'required' => false
                ]
            )
            ->add('description', TextareaType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 600 ] ) ] ] )
            ->add('citation', TextType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('couleur', ColorType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Classe::class,
        ]);
    }
}
