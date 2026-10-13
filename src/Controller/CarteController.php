<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Repository\LieuRepository;
use App\Service\ClasseurHistorique;
use App\Service\Localisateur;
use App\Service\Visibility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteController extends AbstractController
{
    private const HISTORIQUE_PANNEAU = 3;

    /**
     * @Route("/carte", name="carte")
     */
    public function afficherCarte(Request $request, LieuRepository $lieuRepository, Localisateur $localisateur, Visibility $visibility): Response
    {
        $principal = $this->getUser() ? $this->getUser()->getMainCharacter() : null;
        $chemin = $localisateur->chemin($principal);

        $cible = $lieuRepository->find($request->query->getInt('lieu'));
        if ($cible !== null && ($cible->getLocX() === null || $cible->getLocY() === null || !$visibility->isListed($cible))) {
            $cible = null;
        }

        return $this->render('carte/index.html.twig', [
            'lieux' => $lieuRepository->findAllSorted(),
            'positions' => $localisateur->positions(),
            'chemin' => $chemin,
            'cible' => $cible,
            'position_principal' => $chemin[0] ?? null,
        ]);
    }

    /**
     * @Route("/carte/lieu/{id}", name="carte_lieu")
     */
    public function afficherPanneau(Lieu $lieu, Visibility $visibility, Localisateur $localisateur, ClasseurHistorique $classeur): Response
    {
        if (!$visibility->isListed($lieu)) {
            throw $this->createNotFoundException();
        }

        $historique = [];
        if ($visibility->isReadable($lieu)) {
            $historique = array_values(array_filter($classeur->pourLieu($lieu), function ($bloc) use ($visibility) {
                return $visibility->isReadable($bloc['episode']);
            }));
        }

        return $this->render('carte/panel.html.twig', [
            'lieu' => $lieu,
            'presents' => $localisateur->positions()[$lieu->getId()] ?? [],
            'historique' => array_slice($historique, 0, self::HISTORIQUE_PANNEAU),
        ]);
    }
}
