<?php

namespace App\Form;

use App\Entity\Saison;
use App\Entity\Chapitre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Validator\ImageFile;

class AdminChapterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', IntegerType::class)
            ->add('prologue', CheckboxType::class, ['required' => false, 'label' => 'Prologue de saison'])
            ->add('titre', TextType::class)
            ->add('citation', TextareaType::class, ['required' => false, 'empty_data' => null])
            ->add('image', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new ImageFile()],
            ])
            ->add('couleur', ColorType::class)
            ->add('saisonParent', EntityType::class, [
                'class' => Saison::class,
                'choice_label' => 'titre'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitre::class,
        ]);
    }
}
