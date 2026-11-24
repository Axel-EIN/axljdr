<?php

namespace App\Form;

use App\Entity\Personnage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PlayerCharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Profil public',
                'attr' => ['rows' => 12],
                'constraints' => [new Length(['max' => PlayerSheetType::TEXT_MAX_LENGTH])],
            ])
            ->add('playerNotes', TextareaType::class, [
                'required' => false,
                'label' => 'Notes de background pour le Maître de Jeu',
                'attr' => ['rows' => 8],
                'constraints' => [new Length(['max' => PlayerSheetType::TEXT_MAX_LENGTH])],
            ])
        ;

        if ($options['is_gm']) {
            $builder->add('gmNotes', TextareaType::class, [
                'required' => false,
                'label' => 'Notes privées du Maître de Jeu',
                'attr' => ['rows' => 8],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnage::class,
            'is_gm' => false,
        ]);
    }
}
