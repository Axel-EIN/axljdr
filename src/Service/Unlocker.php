<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;
use App\Entity\Unlock;
use App\Repository\ParticipationRepository;
use App\Repository\UnlockRepository;
use Doctrine\ORM\EntityManagerInterface;

class Unlocker
{
    private $entityManager;
    private $unlockRepository;
    private $participationRepository;
    private $enAttente = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        UnlockRepository $unlockRepository,
        ParticipationRepository $participationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->unlockRepository = $unlockRepository;
        $this->participationRepository = $participationRepository;
    }

    public function unlock(Personnage $personnage, $element, bool $parRencontre = false, ?\DateTimeInterface $quand = null): void
    {
        $key = EntityRegistry::keyOf($element);

        if ($key === null || $element->getId() === null) {
            return;
        }

        $ligne = $personnage->getId() . '|' . $key . '|' . $element->getId();

        if (isset($this->enAttente[$ligne])) {
            return;
        }

        $existante = $this->unlockRepository->findOneBy([
            'character' => $personnage,
            'entity' => $key,
            'elementId' => $element->getId(),
        ]);

        if ($existante !== null) {
            return;
        }

        $this->enAttente[$ligne] = true;

        $unlock = new Unlock();
        $unlock->setCharacter($personnage);
        $unlock->setEntity($key);
        $unlock->setElementId($element->getId());
        $unlock->setUnlockedAt($quand ?? new \DateTime());
        $unlock->setByMeeting($parRencontre);

        $this->entityManager->persist($unlock);
    }

    public function syncAccess($element, ?\DateTimeInterface $quand = null): void
    {
        $rencontres = $this->playersWhoMet($element);

        if (empty($rencontres)) {
            return;
        }

        if ($element->getAccess() === Access::AUTO) {
            foreach ($rencontres as $joueur) {
                $this->unlock($joueur, $element, true, $quand);
            }

            return;
        }

        $this->unlockRepository->deleteMeetings(EntityRegistry::keyOf($element), $element->getId());
    }

    private function playersWhoMet($element): array
    {
        if ($element instanceof Personnage) {
            return $this->participationRepository->findPlayersWhoMet($element);
        }

        if ($element instanceof Lieu) {
            return $this->participationRepository->findPlayersWhoVisited($element);
        }

        return [];
    }

    public function syncScene(Scene $scene): void
    {
        $joueurs = [];
        $croises = [];

        foreach ($scene->getParticipations() as $participation) {
            $personnage = $participation->getPersonnage();

            if ($participation->getEstPj()) {
                $joueurs[] = $personnage;
            }

            $croises[] = $personnage;
        }

        if ($scene->getLieu() !== null) {
            $croises[] = $scene->getLieu();
        }

        foreach ($joueurs as $joueur) {
            foreach ($croises as $element) {
                if ($element !== $joueur && $element->getAccess() === Access::AUTO) {
                    $this->unlock($joueur, $element, true);
                }
            }
        }
    }

    public function charactersOf($element): array
    {
        $key = EntityRegistry::keyOf($element);

        if ($key === null || $element->getId() === null) {
            return [];
        }

        $personnages = [];
        foreach ($this->unlockRepository->findForElement($key, $element->getId()) as $unlock) {
            if (!$unlock->isByMeeting()) {
                $personnages[] = $unlock->getCharacter();
            }
        }

        return $personnages;
    }

    public function sync($element, $personnages): void
    {
        $key = EntityRegistry::keyOf($element);

        if ($key === null || $element->getId() === null) {
            return;
        }

        $voulus = [];
        foreach ($personnages as $personnage) {
            $voulus[$personnage->getId()] = $personnage;
        }

        foreach ($this->unlockRepository->findForElement($key, $element->getId()) as $unlock) {
            $id = $unlock->getCharacter()->getId();

            if (isset($voulus[$id])) {
                $unlock->setByMeeting(false);
                unset($voulus[$id]);
            } elseif (!$unlock->isByMeeting()) {
                $this->entityManager->remove($unlock);
            }
        }

        foreach ($voulus as $personnage) {
            $this->unlock($personnage, $element);
        }
    }

    public function forget($element): void
    {
        $key = EntityRegistry::keyOf($element);

        if ($key !== null && $element->getId() !== null) {
            $this->unlockRepository->deleteForElement($key, $element->getId());
        }
    }
}
