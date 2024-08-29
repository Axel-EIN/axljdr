<?php

namespace App\Controller;

use App\Entity\Saison;
use App\Entity\Episode;
use App\Service\ClasseurXP;
use App\Repository\SaisonRepository;
use App\Repository\ChapitreRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AventureController extends AbstractController
{
    /**
     * @Route("/", name="aventure")
     */
    public function viewAventure(SaisonRepository $saisonRepository): Response
    {
        $premiereSaison = $saisonRepository->findOneBy(array(), array('numero' => 'ASC'));
        return $this->redirectToRoute('aventure_saison', [ 'id' => $premiereSaison->getId() ]);
    }

    /**
     * @Route("/aventure/{id}", name="aventure_saison")
     */
    public function viewSaison(Saison $saison, SaisonRepository $saisonRepository, ClasseurXP $classeurXP): Response
    {
        $precedent = $saisonRepository->findPrevious($saison->getNumero());
        $suivant = $saisonRepository->findNext($saison->getNumero());
        $lastNumero = $saisonRepository->findOneBy(array(), array('numero' => 'DESC'))->getNumero();
        $user = $this->getUser();
        $user_personnages_cumul_episodes = [];
        if ($user != null) {
            $user_personnages = $user->getPersonnages();

            foreach($user_personnages as $un_personnage) {
                foreach($saison->getChapitres() as $un_chapitre) {
                    foreach($un_chapitre->getEpisodes() as $un_episode) {
                        if ($classeurXP->cumulUnPersoEpisode($un_episode, $un_personnage->getId()))
                            $user_personnages_cumul_episodes[$un_personnage->getId()][$un_episode->getId()] = $classeurXP->cumulUnPersoEpisode($un_episode, $un_personnage->getId());
                    }
                }      
            }
        }

        return $this->render('aventure/index.html.twig', [
            'saison' => $saison,
            'saison_precedente' => $precedent,
            'saison_suivante' => $suivant,
            'last_numero' => $lastNumero,
            'user_personnages_cumul_episodes' => $user_personnages_cumul_episodes
        ]);
    }

    /**
     * @Route("/aventure/episode/{id}", name="aventure_episode")
     */
    public function viewEpisode(Episode $episode, EpisodeRepository $episodeRepository, ClasseurXP $classeurXP, ChapitreRepository $chapitreRepository): Response
    {
        $precedent = $episodeRepository->findPrevious($episode->getChapitreParent()->getId(), $episode->getNumero());
        $suivant = $episodeRepository->findNext($episode->getChapitreParent()->getId(), $episode->getNumero());
        $classement_episode = $classeurXP->classerPersosEpisode($episode);

        $chapitre_suivant = [];
        $chapitre_suivant = $chapitreRepository->findNext($episode->getChapitreParent()->getSaisonParent()->getId(), $episode->getChapitreParent()->getNumero());

        if (empty($chapitre_suivant))
            $chapitre_suivant = [];

        if (!empty($chapitre_suivant) && !empty($chapitre_suivant->getEpisodes()[0]))
            $episode_chapitre_suivant = $chapitre_suivant->getEpisodes()[0];
        else
            $episode_chapitre_suivant = [];

        return $this->render('aventure/un-episode.html.twig', [
            'episode' => $episode,
            'episode_precedent' => $precedent,
            'episode_suivant' => $suivant,
            'episode_chapitre_suivant' => $episode_chapitre_suivant,
            'classement_episode' => $classement_episode,
        ]);
    }
}