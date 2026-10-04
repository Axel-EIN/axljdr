<?php

namespace App\Controller;

use App\Service\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private const SUGGESTIONS = 8;

    /**
     * @Route("/search", name="search")
     */
    public function index(Request $request, Search $search): Response
    {
        $term = trim((string) $request->query->get('q', ''));

        return $this->render('search/index.html.twig', [
            'term' => $term,
            'groups' => $search->find($term),
            'tooShort' => mb_strlen($term) < Search::MIN_LENGTH,
        ]);
    }

    /**
     * @Route("/search/suggest", name="search_suggest")
     */
    public function suggest(Request $request, Search $search): Response
    {
        $term = trim((string) $request->query->get('q', ''));

        return $this->render('search/suggestions.html.twig', [
            'term' => $term,
            'results' => $search->suggest($term, self::SUGGESTIONS),
        ]);
    }
}
