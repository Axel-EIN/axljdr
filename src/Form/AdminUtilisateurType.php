<?php

namespace App\Form;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\File;

class AdminUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class)
            ->add('roles', ChoiceType::class, [
                'label' => 'rôles',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => [
                    'Joueur' => 'ROLE_JOUEUR',
                    'Maître du Jeu' => 'ROLE_MJ',
                    'Administrateur' => 'ROLE_ADMIN',
                ]
            ])
            ->add('password', TextType::class)
            ->add('email', EmailType::class)
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Compte vérifié — autorise la connexion sans confirmation par e-mail',
                'required' => false,
            ])
            ->add('avatar', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new File(['maxSize' => '5M'])],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $utilisateur = $event->getData();

            $event->getForm()->add('mainCharacter', EntityType::class, [
                'label' => 'personnage principal',
                'class' => Personnage::class,
                'choices' => $utilisateur ? $utilisateur->getPersonnages() : [],
                'choice_label' => fn (Personnage $personnage) => $personnage->getNom() . ' ' . $personnage->getPrenom(),
                'placeholder' => 'Automatique — le plus récent',
                'required' => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
