<?php

namespace App\Form;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use App\Validator\StrongPassword;
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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Validator\ImageFile;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = ($options['data'] ?? null)?->getId() === null;

        $builder
            ->add('pseudo', TextType::class)
            ->add('roles', ChoiceType::class, [
                'label' => 'rôles',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => [
                    'Joueur' => 'ROLE_PLAYER',
                    'Maître du Jeu' => 'ROLE_GM',
                    'Administrateur' => 'ROLE_ADMIN',
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'mot de passe',
                'mapped' => false,
                'required' => $isNew,
                'help' => $isNew
                    ? '8 à 32 caractères, avec au moins un chiffre, une minuscule et une majuscule.'
                    : 'Laisser vide pour garder le mot de passe actuel.',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => $isNew
                    ? [new NotBlank(['message' => 'Veuillez entrer un mot de passe']), new StrongPassword()]
                    : [new StrongPassword()],
            ])
            ->add('email', EmailType::class)
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Compte vérifié — autorise la connexion sans confirmation par e-mail',
                'required' => false,
            ])
            ->add('avatar', FileType::class, [
                'mapped' => false, 'data_class' => null, 'required' => false,
                'constraints' => [new ImageFile()],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();

            $event->getForm()->add('mainCharacter', EntityType::class, [
                'label' => 'personnage principal',
                'class' => Personnage::class,
                'choices' => $user ? $user->getPersonnages() : [],
                'choice_label' => 'nomComplet',
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
