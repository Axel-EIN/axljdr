<?php

namespace App\Form;

use App\Entity\Development;
use App\Entity\Participation;
use App\Entity\Personnage;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminDevelopmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('personnage', EntityType::class, [
                'class' => Personnage::class,
                'mapped' => false,
                'label' => 'Personnage',
                'placeholder' => '— Choisir un personnage —',
                'choice_label' => fn(Personnage $p) => trim($p->getPrenom() . ' ' . $p->getNom()),
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('p')
                    ->innerJoin('p.participations', 'part')
                    ->groupBy('p.id')
                    ->addOrderBy('p.estPj', 'DESC')
                    ->addOrderBy('p.prenom', 'ASC'),
            ])
            ->add('participation', EntityType::class, [
                'class' => Participation::class,
                'label' => 'Scène',
                'placeholder' => '— Choisir une scène —',
                'choice_label' => fn(Participation $p) => self::libelleScene($p),
                'group_by' => fn(Participation $p) => self::libelleChapitre($p),
                'choice_attr' => fn(Participation $p) => ['data-personnage' => $p->getPersonnage()->getId()],
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('part')
                    ->join('part.personnage', 'p')
                    ->join('part.scene', 's')
                    ->join('s.episodeParent', 'e')
                    ->join('e.chapitreParent', 'c')
                    ->join('c.saisonParent', 'sai')
                    ->addOrderBy('sai.numero', 'DESC')
                    ->addOrderBy('c.numero', 'DESC')
                    ->addOrderBy('e.numero', 'DESC')
                    ->addOrderBy('s.numero', 'DESC'),
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Développement',
                'attr' => ['rows' => 10],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Development::class,
        ]);
    }

    private static function libelleScene(Participation $participation): string
    {
        $scene = $participation->getScene();

        return sprintf(
            'E%d — Scène %d : %s%s',
            $scene->getEpisodeParent()->getNumero(),
            $scene->getNumero(),
            $scene->getTitre(),
            $participation->getEstMort() ? ' (mort)' : ''
        );
    }

    private static function libelleChapitre(Participation $participation): string
    {
        $chapitre = $participation->getScene()->getEpisodeParent()->getChapitreParent();

        return sprintf(
            'Saison %d — Chapitre %d : %s',
            $chapitre->getSaisonParent()->getNumero(),
            $chapitre->getNumero(),
            $chapitre->getTitre()
        );
    }
}
