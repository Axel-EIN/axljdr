<?php

namespace App\Form;

use App\Entity\Access;
use App\Entity\Personnage;
use App\Repository\PersonnageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class PublishableFields
{
    public static function add(FormBuilderInterface $builder): void
    {
        $builder
            ->add('access', ChoiceType::class, [
                'label' => 'Accès',
                'choices' => array_flip(Access::CHOICES),
            ])
            ->add('publishedAt', DateType::class, [
                'label' => 'Publié le',
                'widget' => 'single_text',
                'required' => false,
                'help' => 'Vide sur un élément public : socle initial, visible mais absent de l\'accueil. Datée : il paraît au fil des nouveautés et peut porter le badge Nouveau.',
            ])
            ->add('clearPublishedAt', CheckboxType::class, [
                'label' => 'Effacer la date de publication',
                'mapped' => false,
                'required' => false,
            ])
            ->add('unlockedBy', EntityType::class, [
                'label' => 'Débloqué par',
                'class' => Personnage::class,
                'query_builder' => function (PersonnageRepository $personnageRepository) {
                    return $personnageRepository->createQueryBuilder('p')
                        ->andWhere('p.estPj = true')
                        ->orderBy('p.nom', 'ASC')
                        ->addOrderBy('p.prenom', 'ASC');
                },
                'choice_label' => function (Personnage $personnage) {
                    return trim($personnage->getNom() . ' ' . $personnage->getPrenom());
                },
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'required' => false,
            ]);

        $publishedBefore = null;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use (&$publishedBefore) {
            $publishedBefore = $event->getData() ? $event->getData()->getPublishedAt() : null;
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use (&$publishedBefore) {
            $entity = $event->getData();
            $publishedAt = $event->getForm()->get('clearPublishedAt')->getData() ? null : $entity->getPublishedAt();

            if ($publishedAt !== null) {
                $now = new \DateTime();
                $publishedAt = $publishedBefore !== null && $publishedBefore->format('Y-m-d') === $publishedAt->format('Y-m-d')
                    ? $publishedBefore
                    : $publishedAt->setTime((int) $now->format('H'), (int) $now->format('i'));
            }

            $entity->setPublishedAt($publishedAt);
        });
    }
}
