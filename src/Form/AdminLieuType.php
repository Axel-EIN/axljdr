<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Clan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminLieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('surnom', TextType::class)
            ->add('icone', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('carte', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('region', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('description', TextareaType::class)
            ->add('quartiers', TextareaType::class, array('required' => false))
            ->add('locX', NumberType::class, array(
                'scale' => 3,
                'label' => 'Coordinate (Horizontal % of the map)',
                'required' => false,
            ))
            ->add('locY', NumberType::class, array(
                'scale' => 2,
                'attr' => array(
                    'min' => 1,
                    'max' => 99,
                    'step' => '.05',
                ),
                'label' => 'Coordinate (Vertical % of the map)',
                'required' => false,
            ))
            ->add('clan', EntityType::class, [
                'class' => Clan::class,
                'choice_label' => 'nom',
                'placeholder' => 'Non défini',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
