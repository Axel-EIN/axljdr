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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminLieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('surnom', TextType::class)
            ->add('icone', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('type', ChoiceType::class, [
                'placeholder' => 'Pas de type défini',
                'required' => false,
                'choices'  => [
                    'Village' => 'Village',
                    "Ville" => "Ville",
                    'Cité' => 'Cité',
                    'Région ou Royaume' => 'Région ou Royaume',
                    'Shiro (Fort ou Château)' => 'Shiro (Fort ou Château)',
                    'Kyuden (Palais)' => 'Kyuden (Palais)',
                ],
            ])
            ->add('region', FileType::class, array(
                'mapped' => false,
                'data_class' => null,
                'required' => false,
                'label' => 'Carte de la région',
            ))
            ->add('carte', FileType::class, array(
                'mapped' => false,
                'data_class' => null,
                'required' => false,
                'label' => 'Plan Intérieur',
            ))
            ->add('description', TextareaType::class, array('required' => false))
            ->add('quartiers', TextareaType::class, array('required' => false))
            ->add('locX', NumberType::class, array(
                'scale' => 2,
                'attr' => array(
                    'min' => 1,
                    'max' => 99,
                    'step' => '.05',
                ),
                'label' => 'Coord. % hori. carte',
                'required' => false,
            ))
            ->add('locY', NumberType::class, array(
                'scale' => 2,
                'attr' => array(
                    'min' => 1,
                    'max' => 99,
                    'step' => '.05',
                ),
                'label' => 'Coord. % vert. carte',
                'required' => false,
            ))
            ->add('population', NumberType::class, array(
                'label' => 'Population',
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
