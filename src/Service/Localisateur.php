<?php
namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Participation;
use App\Entity\Personnage;
use App\Entity\Scene;
use App\Repository\FichePersonnageRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;

class Localisateur
{
    private $participationRepository;
    private $fichePersonnageRepository;
    private $visibility;
    private $entityManager;
    private $lieuRepository;
    private $parcours;
    private $libres;

    public function __construct(ParticipationRepository $participationRepository, FichePersonnageRepository $fichePersonnageRepository, Visibility $visibility, EntityManagerInterface $entityManager, LieuRepository $lieuRepository)
    {
        $this->participationRepository = $participationRepository;
        $this->fichePersonnageRepository = $fichePersonnageRepository;
        $this->visibility = $visibility;
        $this->entityManager = $entityManager;
        $this->lieuRepository = $lieuRepository;
    }

    public function positions(): array
    {
        $positions = [];
        foreach ($this->joueurs() as $joueur) {
            if ($joueur['lieu'] !== null) {
                $positions[$joueur['lieu']->getId()][] = $joueur['personnage'];
            }
        }

        return $positions;
    }

    public function joueurs(): array
    {
        $joueurs = [];
        $libres = $this->libres();
        foreach ($this->parcours() as $id => $parcours) {
            if (!isset($libres[$id])) {
                $lieu = $parcours['lieux'][0];
                $joueurs[] = ['personnage' => $parcours['personnage'], 'lieu' => $lieu, 'locX' => $lieu->getLocX(), 'locY' => $lieu->getLocY()];
            }
        }

        foreach ($libres as $libre) {
            $joueurs[] = ['personnage' => $libre['personnage'], 'lieu' => null, 'locX' => $libre['locX'], 'locY' => $libre['locY']];
        }

        return $joueurs;
    }

    public function libres(): array
    {
        if ($this->libres !== null) {
            return $this->libres;
        }

        $this->libres = $this->listerLibres($this->fichePersonnageRepository->findPlacedForLivingPlayers());

        return $this->libres;
    }

    public function positionLibre(?Personnage $personnage): ?array
    {
        if ($personnage === null) {
            return null;
        }

        $libres = $this->libres ?? $this->listerLibres($this->fichePersonnageRepository->findPlacedForLivingPlayers($personnage));

        return $libres[$personnage->getId()] ?? null;
    }

    public function chemin(?Personnage $personnage, int $longueur = 6): array
    {
        if ($personnage === null) {
            return [];
        }

        $etapes = array_map(function (Lieu $lieu) {
            return ['locX' => $lieu->getLocX(), 'locY' => $lieu->getLocY()];
        }, $this->parcours()[$personnage->getId()]['lieux'] ?? []);
        $libre = $this->positionLibre($personnage);

        if ($libre !== null) {
            array_unshift($etapes, ['locX' => $libre['locX'], 'locY' => $libre['locY']]);
        }

        return array_slice($etapes, 0, $longueur);
    }

    public function effacerPositionsLibres(Scene $scene, ?array $personnages = null): array
    {
        $lieu = $scene->getLieu();
        if ($lieu === null || !$lieu->isPlaced()) {
            return [];
        }

        if ($personnages === null) {
            $personnages = array_map(function (Participation $participation) {
                return $participation->getPersonnage();
            }, $this->participationRepository->findBy(['scene' => $scene]));
        }

        $effaces = [];
        foreach ($personnages as $personnage) {
            $fiche = $personnage->getFichePersonnage();
            if ($fiche === null || !$fiche->hasPositionLibre()) {
                continue;
            }

            $derniere = $this->participationRepository->findLastForCharacter($personnage);
            if ($derniere !== null && $derniere->getScene()->getId() === $scene->getId()) {
                $fiche->setLocX(null)->setLocY(null);
                $effaces[] = $personnage;
            }
        }

        if ($effaces !== []) {
            $this->entityManager->flush();
            $this->libres = null;
        }

        return $effaces;
    }

    public function position(?Personnage $personnage): ?Lieu
    {
        return $personnage === null ? null : $this->parcours()[$personnage->getId()]['lieux'][0] ?? null;
    }

    public function carte(?Personnage $personnage, bool $lieuMasqueInclus = false): array
    {
        $positionLibre = $this->positionLibre($personnage);
        $positionActuelle = $positionLibre === null ? $this->position($personnage) : null;
        $positionConnue = $positionActuelle !== null && ($lieuMasqueInclus || $this->visibility->isListed($positionActuelle));
        $carteVisible = $positionLibre !== null || $positionConnue;

        return [
            'position_libre' => $positionLibre,
            'position_actuelle' => $positionActuelle,
            'position_connue' => $positionConnue,
            'carte_visible' => $carteVisible,
            'lieux' => $carteVisible ? $this->lieuRepository->findAllSorted() : [],
        ];
    }

    private function listerLibres(array $fiches): array
    {
        $libres = [];
        foreach ($fiches as $fiche) {
            $personnage = $fiche->getPersonnage();

            if ($this->visibility->isListed($personnage)) {
                $libres[$personnage->getId()] = [
                    'personnage' => $personnage,
                    'locX' => $fiche->getLocX(),
                    'locY' => $fiche->getLocY(),
                ];
            }
        }

        return $libres;
    }

    private function parcours(): array
    {
        if ($this->parcours !== null) {
            return $this->parcours;
        }

        $this->parcours = [];
        foreach ($this->participationRepository->findPlacedForLivingPlayers() as $participation) {
            $personnage = $participation->getPersonnage();
            $scene = $participation->getScene();

            if (!$this->visibility->isReadable($scene->getEpisodeParent()) || !$this->visibility->isListed($personnage)) {
                continue;
            }

            $id = $personnage->getId();
            $lieux = $this->parcours[$id]['lieux'] ?? [];
            $dernier = end($lieux);

            $this->parcours[$id]['personnage'] = $personnage;
            if ($dernier === false || $dernier->getId() !== $scene->getLieu()->getId()) {
                $this->parcours[$id]['lieux'][] = $scene->getLieu();
            }
        }

        return $this->parcours;
    }
}
