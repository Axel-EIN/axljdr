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
use Symfony\Component\Validator\Constraints\Length;

class AdminFamilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, array( 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ) )
            ->add('mon', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('description', TextareaType::class, array( 'required' => false, 'constraints' => [ new Length( [ 'max' => 1000 ] ) ] ))
            ->add('bonus', TextType::class, array( 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ) )
            ->add('clan', EntityType::class, [
                'class' => Clan::class,
                'choice_label' => 'nom',
                'placeholder' => 'Non défini',
                'required' => false
                ])
            ->add('chef', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'prenom',
                'group_by' => 'clan.nom',
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
