<?php

namespace App\Form;

use App\Entity\Library;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminLibraryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('base', CheckboxType::class, [
                'required' => false])
            ->add('numero', IntegerType::class)
            ->add('entity', ChoiceType::class, [
                'required' => false,
                'label' => 'Bibliothèque', 
                'choices'  => [
                    'Avantages / Désavantages' => 'avantage',
                    'Compétences' => 'competence',
                    'Objets' => 'objet',
                    'Sorts' => 'sort',
                    'Kihos' => 'kiho',
                    'Sorts de Mahos' => 'maho',
                    'Tatouages' => 'tatoo',
                ],
            ])
            ->add('tabField', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('subTabField', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('filterField', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('keyword1Field', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('keyword2Field', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('keyword3Field', TextType::class, [
                'required' => false,
                'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('mixable', CheckboxType::class, [
                'required' => false])
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
                'data_class' => null ] )
            ->add('pdf', FileType::class, [
                'required' => false,
                'mapped' => false,
                'data_class' => null ] )
            ->add('description', TextareaType::class, [
                'required' => false ] )
            ->add('aside', TextareaType::class, [
                'required' => false ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Library::class,
        ]);
    }
}
