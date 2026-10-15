<?php

namespace App\Controller;

use App\Entity\Saison;
use App\Entity\Episode;
use App\Service\ClasseurXP;
use App\Service\Visibility;
use App\Repository\SaisonRepository;
use App\Repository\ChapitreRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ParticipationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AventureController extends AbstractController
{
    use VisibilityTrait;

    /**
     * @Route("/aventure", name="aventure")
     */
    public function viewAventure(SaisonRepository $saisonRepository): Response
    {
        $premiereSaison = $saisonRepository->findCourante();

        if ($premiereSaison === null) {
            return $this->render('aventure/aucune-saison.html.twig');
        }

        return $this->redirectToRoute('aventure_saison', [ 'id' => $premiereSaison->getId() ]);
    }

    /**
     * @Route("/aventure/{id}", name="aventure_saison")
     */
    public function viewSaison(Saison $saison, SaisonRepository $saisonRepository, ParticipationRepository $participationRepository, ClasseurXP $classeurXP): Response
    {
        $precedent = $saisonRepository->findPrevious($saison->getNumero());
        $suivant = $saisonRepository->findNext($saison->getNumero());
        $user = $this->getUser();
        $user_personnages_cumul_episodes = [];
        if ($user != null) {
            $ids = $user->getPersonnages()->map(fn ($un_personnage) => $un_personnage->getId())->toArray();
            $user_personnages_cumul_episodes = $classeurXP->cumulsParEpisode(
                $participationRepository->findForCharactersInSaison($saison, $ids),
                $ids
            );
        }

        return $this->render('aventure/index.html.twig', [
            'saison' => $saison,
            'saison_precedente' => $precedent,
            'saison_suivante' => $suivant,
            'user_personnages_cumul_episodes' => $user_personnages_cumul_episodes
        ]);
    }

    /**
     * @Route("/aventure/episode/{id}", name="aventure_episode")
     */
    public function viewEpisode(Episode $episode, EpisodeRepository $episodeRepository, ClasseurXP $classeurXP, ChapitreRepository $chapitreRepository, Visibility $visibility): Response
    {
        if ($response = $this->accessGuard($visibility, $episode, 'episode', 'aventure')) {
            return $response;
        }

        $suivant = $episodeRepository->findNext($episode->getChapitreParent()->getId(), $episode->getNumero());
        $classement_episode = $classeurXP->classerPersosEpisode($episode);

        $chapitre_precedent = $chapitreRepository->findPrevious($episode->getChapitreParent()->getSaisonParent()->getId(), $episode->getChapitreParent()->getNumero());

        $chapitre_suivant = [];
        $chapitre_suivant = $chapitreRepository->findNext($episode->getChapitreParent()->getSaisonParent()->getId(), $episode->getChapitreParent()->getNumero());

        if (empty($chapitre_suivant))
            $chapitre_suivant = [];

        if (!empty($chapitre_suivant) && !empty($chapitre_suivant->getEpisodes()[0]))
            $episode_chapitre_suivant = $chapitre_suivant->getEpisodes()[0];
        else
            $episode_chapitre_suivant = [];

        return $this->render('aventure/episode-detail.html.twig', [
            'episode' => $episode,
            'episode_suivant' => $suivant,
            'episode_chapitre_suivant' => $episode_chapitre_suivant,
            'chapitre_precedent' => $chapitre_precedent,
            'chapitre_suivant' => $chapitre_suivant,
            'classement_episode' => $classement_episode,
        ]);
    }
}