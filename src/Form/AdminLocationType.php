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
use Symfony\Component\Validator\Constraints\File;

class AdminLocationType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $types = array_keys(Lieu::TYPE_ICONS);

    $builder
      ->add('nom', TextType::class)
      ->add('surnom', TextType::class, [ 'required' => false ])
      ->add('icone', FileType::class, [
        'mapped' => false, 'data_class' => null, 'required' => false,
        'constraints' => [new File(['maxSize' => '5M'])],
      ])
      ->add('image', FileType::class, [
        'mapped' => false, 'data_class' => null, 'required' => false,
        'constraints' => [new File(['maxSize' => '5M'])],
      ])
      ->add('type', ChoiceType::class, [
        'placeholder' => 'Pas de type défini',
        'required' => false,
        'choices'  => array_combine($types, $types),
      ])
      ->add('planMap', FileType::class, [
        'mapped' => false,
        'data_class' => null,
        'required' => false,
        'label' => 'Plan',
        'constraints' => [new File(['maxSize' => '5M'])],
      ])
      ->add('description', TextareaType::class, array('required' => false))
      ->add('planDesc', TextareaType::class, [
        'label' => 'Description du plan',
        'required' => false,
      ])
      ->add('locX', MapCoordinateType::class, ['label' => 'Coord. % hori. carte'])
      ->add('locY', MapCoordinateType::class, ['label' => 'Coord. % vert. carte'])
      ->add('zoom', NumberType::class, [
        'scale' => 2,
        'attr' => ['min' => 0.25, 'max' => 2, 'step' => '.05'],
        'label' => 'Zoom carte (0,25 à 2)',
        'required' => false,
      ])
      ->add('regionDesc', TextareaType::class, [
        'label' => 'Description de la région',
        'required' => false,
      ])
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

    PublishableFields::add($builder);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Lieu::class,
    ]);
  }
}
