<?php

namespace App\Form;

use App\Entity\Lore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminLoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('numero', IntegerType::class)
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('pdf', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('part1titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part1', TextareaType::class, ['required' => false])
            ->add('part1aside', textareaType::class, ['required' => false])
            ->add('part2titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part2', TextareaType::class, ['required' => false])
            ->add('part2aside', textareaType::class, ['required' => false])
            ->add('part3titre', TextType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('part3', TextareaType::class, ['required' => false])
            ->add('part3aside', textareaType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lore::class,
        ]);
    }
}
