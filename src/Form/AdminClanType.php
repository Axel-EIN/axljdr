<?php

namespace App\Form;

use App\Entity\Clan;
use App\Entity\FactionType;
use App\Entity\Personnage;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class AdminClanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 'constraints' => [ new Length( [ 'max' => 30 ] ) ] ] )
            ->add('genre', ChoiceType::class, [
                'placeholder' => 'Pas de genre défini',
                'required' => false,
                'choices'  => [
                    'Masculin' => 'M',
                    'Féminin' => 'F'
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => array_flip(FactionType::LABELS),
            ])
            ->add('citation', TextType::class, [ 'required' => false, 'constraints' => [ new Length( [ 'max' => 100 ] ) ] ] )
            ->add('description', TextareaType::class, ['required' => false, 'constraints' => [ new Length( [ 'max' => 600 ] ) ] ] )
            ->add('longDescription', TextareaType::class, ['required' => false] )
            ->add('couleur', ColorType::class)
            ->add('mon', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new File(['maxSize' => '5M'])],
            ])
            ->add('image', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new File(['maxSize' => '5M'])],
            ])
            ->add('video', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new File(['maxSize' => '50M'])],
            ])
            ->add('territoireDesc', TextareaType::class, ['required' => false])
            ->add('locX', MapCoordinateType::class, ['label' => 'Coord. % hori. carte'])
            ->add('locY', MapCoordinateType::class, ['label' => 'Coord. % vert. carte'])
            ->add('zoom', NumberType::class, [
                'scale' => 2, 'required' => false, 'label' => 'Zoom carte (0,25 à 2)',
                'attr' => ['min' => 0.25, 'max' => 2, 'step' => '.05'],
            ])
            ->add('emblemX', MapCoordinateType::class, ['label' => 'Coord. % hori. mon'])
            ->add('emblemY', MapCoordinateType::class, ['label' => 'Coord. % vert. mon'])
            ->add('chef', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'prenom',
                'group_by' => 'clan.nom',
                'placeholder' => 'Non défini',
                'required' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('p')
                    ->leftJoin('p.clan', 'c')->orderBy('c.nom', 'ASC')->addOrderBy('p.prenom', 'ASC'),
            ])
        ;

        PublishableFields::add($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clan::class,
        ]);
    }
}
