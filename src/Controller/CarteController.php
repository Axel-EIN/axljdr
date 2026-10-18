<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Personnage;
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

        return $this->render('carte/panel.html.twig', [
            'lieu' => $lieu,
            'presents' => $localisateur->positions()[$lieu->getId()] ?? [],
            'historique' => $visibility->isReadable($lieu) ? $this->historiqueLisible($classeur->pourLieu($lieu), $visibility) : [],
        ]);
    }

    /**
     * @Route("/carte/personnage/{id}", name="carte_personnage")
     */
    public function afficherPanneauPersonnage(Personnage $personnage, Visibility $visibility, ClasseurHistorique $classeur): Response
    {
        if (!$visibility->isListed($personnage)) {
            throw $this->createNotFoundException();
        }

        return $this->render('carte/panel.html.twig', [
            'personnage' => $personnage,
            'historique' => $visibility->isReadable($personnage) ? $this->historiqueLisible($classeur->pourPersonnage($personnage), $visibility) : [],
        ]);
    }

    private function historiqueLisible(array $historique, Visibility $visibility): array
    {
        $lisible = array_filter($historique, function ($bloc) use ($visibility) {
            return $visibility->isReadable($bloc['episode']);
        });

        return array_slice(array_values($lisible), 0, self::HISTORIQUE_PANNEAU);
    }
}
