<?php

namespace App\Form;

use App\Entity\Famille;
use App\Entity\Clan;
use App\Entity\Personnage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminFamilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('description', TextareaType::class)
            ->add('mon', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('clan', EntityType::class, [
                'class' => Clan::class,
                'choice_label' => 'nom',
                'placeholder' => 'Non défini',
                'required' => false
                ])
            ->add('chef', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'prenom',
                'placeholder' => 'Non défini',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Famille::class,
        ]);
    }
}
