<?php

namespace App\Form;

use App\Entity\Lore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Validator\ImageFile;
use App\Validator\PdfFile;
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
            ->add('image', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new ImageFile()],
            ])
            ->add('pdf', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new PdfFile()],
            ])
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

        for ($i = 1; $i <= 5; $i++) {
            $builder->add('part' . $i . 'links', PartLinksType::class, ['part' => $i]);
        }

        PublishableFields::add($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lore::class,
        ]);
    }
}
