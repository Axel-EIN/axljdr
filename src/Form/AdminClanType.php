<?php

namespace App\Form;

use App\Entity\Clan;
use App\Entity\Personnage;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            ->add('estMajeur', CheckboxType::class, ['required' => false])
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
            ->add('territoireCarte', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new File(['maxSize' => '5M'])],
            ])
            ->add('territoireDesc', TextareaType::class, ['required' => false])
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

        $builder
            ->get('estMajeur')
            ->addModelTransformer(new CallbackTransformer(
                function ($activeAsString) {
                    return (bool)(int)$activeAsString;
                },
                function ($activeAsBoolean) {
                    return (string)(int)$activeAsBoolean;
                }
            ));

        PublishableFields::add($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clan::class,
        ]);
    }
}
