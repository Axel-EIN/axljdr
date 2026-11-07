<?php

namespace App\Form;

use App\Validator\StrongPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AccountPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['require_current']) {
            $builder->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe actuel',
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre mot de passe actuel.']),
                    new UserPassword(['message' => 'Le mot de passe actuel est incorrect.']),
                ],
            ]);
        }

        $builder
        ->add('plainPassword', RepeatedType::class, [
            'mapped' => false,
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe ne correspondent pas.',
            'required' => true,
            'first_options'  => [
                'help' => 'Au moins un chiffre, une lettre minuscule et majuscule',
                'label' => 'Mot de passe (8 caractères minimum)',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new StrongPassword(),
                ]
            ],
            'second_options' => ['label' => 'Confirmation du mot de passe'],
        ])
        ->add('Modifier', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'require_current' => false,
        ]);
        $resolver->setAllowedTypes('require_current', 'bool');
    }
}
