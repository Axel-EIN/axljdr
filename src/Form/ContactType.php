<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse e-mail.']),
                    new Email(['message' => 'Cette adresse e-mail n\'est pas valide.']),
                ],
            ])
            ->add('objet', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un objet.']),
                    new Length(['max' => 150, 'maxMessage' => 'L\'objet doit contenir au plus {{ limit }} caractères.']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un message.']),
                    new Length([
                        'min' => 10,
                        'max' => 5000,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le message doit contenir au plus {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('website', TextType::class, [
                'label' => false,
                'required' => false,
                'row_attr' => ['class' => 'd-none'],
                'attr' => ['tabindex' => '-1', 'autocomplete' => 'off'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}
