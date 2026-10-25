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

class StoryController extends AbstractController
{
    use VisibilityTrait;

    /**
     * @Route("/story", name="story")
     */
    public function index(SaisonRepository $seasonRepository): Response
    {
        $currentSeason = $seasonRepository->findCourante();

        if ($currentSeason === null) {
            return $this->render('story/no-season.html.twig');
        }

        return $this->redirectToRoute('story_season', [ 'id' => $currentSeason->getId() ]);
    }

    /**
     * @Route("/story/{id}", name="story_season")
     */
    public function showSeason(Saison $season, SaisonRepository $seasonRepository, ParticipationRepository $participationRepository, ClasseurXP $classeurXP): Response
    {
        $previous = $seasonRepository->findPrevious($season->getNumero());
        $next = $seasonRepository->findNext($season->getNumero());
        $user = $this->getUser();
        $userCharactersEpisodeTotals = [];
        if ($user != null) {
            $ids = $user->getPersonnages()->map(fn ($character) => $character->getId())->toArray();
            $userCharactersEpisodeTotals = $classeurXP->cumulsParEpisode(
                $participationRepository->findForCharactersInSaison($season, $ids),
                $ids
            );
        }

        return $this->render('story/index.html.twig', [
            'season' => $season,
            'previous_season' => $previous,
            'next_season' => $next,
            'user_characters_episode_totals' => $userCharactersEpisodeTotals
        ]);
    }

    /**
     * @Route("/story/episode/{id}", name="story_episode")
     */
    public function showEpisode(Episode $episode, EpisodeRepository $episodeRepository, ClasseurXP $classeurXP, ChapitreRepository $chapterRepository, Visibility $visibility): Response
    {
        if ($response = $this->accessGuard($visibility, $episode, 'episode', 'story')) {
            return $response;
        }

        $next = $episodeRepository->findNext($episode->getChapitreParent()->getId(), $episode->getNumero());
        $episodeRanking = $classeurXP->classerPersosEpisode($episode);

        $previousChapter = $chapterRepository->findPrevious($episode->getChapitreParent()->getSaisonParent()->getId(), $episode->getChapitreParent()->getNumero());

        $nextChapter = $chapterRepository->findNext($episode->getChapitreParent()->getSaisonParent()->getId(), $episode->getChapitreParent()->getNumero());
        $nextChapterEpisode = $nextChapter?->getEpisodes()[0];

        return $this->render('story/episode-detail.html.twig', [
            'episode' => $episode,
            'next_episode' => $next,
            'next_chapter_episode' => $nextChapterEpisode,
            'previous_chapter' => $previousChapter,
            'next_chapter' => $nextChapter,
            'episode_ranking' => $episodeRanking,
        ]);
    }
}
