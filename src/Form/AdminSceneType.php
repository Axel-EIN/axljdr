<?php

namespace App\Form;

use App\Entity\Scene;
use App\Entity\Episode;
use App\Entity\Lieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminSceneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('episodeParent', EntityType::class, [
                'class' => Episode::class,
                'choice_label' => 'titre'
            ])
            ->add('numero', IntegerType::class)
            ->add('lieu', EntityType::class, [
                'placeholder' => 'Pas de lieu défini',
                'required' => false,
                'class' => Lieu::class,
                'choice_label' => 'nom'
            ])
            ->add('temps', ChoiceType::class, [
                'placeholder' => 'Pas de temps défini',
                'required' => false,
                'choices'  => [
                    'Aube' => 'Aube',
                    'Matinée' => 'Matinée',
                    "Midi" => "Midi",
                    'Après-midi' => 'Après-midi',
                    'Journée' => 'Journée',
                    'Soirée' => 'Soirée',
                    'Crépuscule' => 'Crépuscule',
                    'Minuit' => 'Minuit',
                    'Nuit' => 'Nuit',
                    'Jours suivants' => 'Jours suivants',
                    'Intemporel' => 'Intemporel',
                ], ])
            ->add('texte', TextareaType::class)
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Scene::class,
        ]);
    }
}
