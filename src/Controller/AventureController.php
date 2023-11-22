<?php

namespace App\Controller;

use App\Entity\Saison;
use App\Entity\Episode;
use App\Service\ParticipeurEpisode;
use App\Repository\SaisonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\PersonnageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AventureController extends AbstractController
{
    /**
     * @Route("/", name="aventure")
     */
    public function afficherAventure(SaisonRepository $saisonRepository): Response
    {
        $premiereSaison = $saisonRepository->findOneBy(array(), array('numero' => 'ASC'));
        return $this->redirectToRoute('aventure_saison', [ 'id' => $premiereSaison->getId() ]);
    }

    /**
     * @Route("/aventure/{id}", name="aventure_saison")
     */
    public function afficherSaison(Saison $saison, SaisonRepository $saisonRepository, ParticipeurEpisode $participeurEpisode): Response
    {
        $precedent = $saisonRepository->findPrevious($saison->getNumero());
        $suivant = $saisonRepository->findNext($saison->getNumero());
        $lastNumero = $saisonRepository->findOneBy(array(), array('numero' => 'DESC'))->getNumero();

        $chapitres_personnages = [];
        $compteur = 0;
        foreach ($saison->getChapitres() as $un_chapitre)
        {
            $episodes_personnages = [];
            $count = 0;
            foreach($un_chapitre->getEpisodes() as $un_episode)
            {
                $episodes_personnages[$count]['episodeId'] = $un_episode->getId();
                $episodes_personnages[$count]['participationsDeEpisode'] = $participeurEpisode->personnagesEpisodes($un_episode);
                $count++;
            }
            $chapitres_personnages[$compteur]['chapitreId'] = $un_chapitre->getId();
            $chapitres_personnages[$compteur]['participationsDuChapitre'] = $episodes_personnages;
            $compteur++;
        }
        
        // dd($chapitres_personnages);

        return $this->render('aventure/index.html.twig', [
            'saison' => $saison,
            'saison_precedente' => $precedent,
            'saison_suivante' => $suivant,
            'last_numero' => $lastNumero,
            'chapitres_personnages' => $chapitres_personnages
        ]);
    }

    /**
     * @Route("/aventure/episode/{id}", name="aventure_episode")
     */
    public function afficherEpisode(Episode $episode, EpisodeRepository $episodeRepository, PersonnageRepository $personnageRepository, ParticipeurEpisode $participeurEpisode): Response
    {
        $precedent = $episodeRepository->findPrevious($episode->getChapitreParent()->getId(), $episode->getNumero());
        $suivant = $episodeRepository->findNext($episode->getChapitreParent()->getId(), $episode->getNumero());

        $episode_personnages = $participeurEpisode->personnagesEpisodes($episode);

        return $this->render('aventure/un-episode.html.twig', [
            'episode' => $episode,
            'episode_precedent' => $precedent,
            'episode_suivant' => $suivant,
            'episode_personnages' => $episode_personnages,
        ]);
    }
}
