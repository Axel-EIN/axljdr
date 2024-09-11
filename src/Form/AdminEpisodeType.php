<?php

namespace App\Form;

use App\Entity\Episode;
use App\Entity\Chapitre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminEpisodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', IntegerType::class)
            ->add('numeroSaison', IntegerType::class)
            ->add('titre', TextType::class)
            ->add('resume', TextareaType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 200 ] ) ]])
            ->add('image', FileType::class, array('mapped' => false, 'data_class' => null, 'required' => false))
            ->add('chapitreParent', EntityType::class, [
                'class' => Chapitre::class,
                'choice_label' => 'titre'
            ])
            ->add('issue', ChoiceType::class, [
                'choices'  => [
                    '' => null,
                    'Réussite' => 'Win',
                    "Échec" => "Lose"]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Episode::class,
        ]);
    }
}
