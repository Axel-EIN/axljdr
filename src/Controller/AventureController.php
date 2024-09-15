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

        // $episode_participations = [];
        // $episode_personnages_id = [];
        // foreach($episode->getScenes() as $une_scene) {
        //     foreach($une_scene->getParticipations() as $une_participation) {
        //         if ($une_participation->getEstPj()) {
        //             $episode_participations[] = $une_participation;
        //             $episode_personnages_id[] = $une_participation->getPersonnage()->getId();
        //         }
        //     }
        // }

        // $episode_personnages_id = array_unique($episode_personnages_id);
        // $compteur = 0;
        // $episode_personnages = [];
        // foreach($episode_personnages_id as $un_personnage_id) {
        //     $compteurXp = 0;
        //     foreach($episode_participations as $une_participation) {
        //         if ($une_participation->getPersonnage()->getId() == $un_personnage_id)
        //             $compteurXp = $compteurXp + $une_participation->getXpGagne();
        //     }
        //     $episode_personnages[$compteur]['prenom'] = $personnageRepository->find($un_personnage_id)->getPrenom();
        //     $episode_personnages[$compteur]['xp'] = $compteurXp;
        //     $compteur++;
        // }

        // usort($episode_personnages, function ($a, $b) {
        //     return strcmp($a['xp'], $b['xp']);
        // } );
        // $episode_personnages = array_reverse($episode_personnages);

        return $this->render('aventure/un-episode.html.twig', [
            'episode' => $episode,
            'episode_precedent' => $precedent,
            'episode_suivant' => $suivant,
            'episode_personnages' => $episode_personnages,
        ]);
    }
}
