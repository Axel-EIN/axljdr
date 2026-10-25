<?php

namespace App\Service;

use App\Entity\Access;
use App\Entity\Lieu;
use App\Entity\Objet;
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
    private $pending = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        UnlockRepository $unlockRepository,
        ParticipationRepository $participationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->unlockRepository = $unlockRepository;
        $this->participationRepository = $participationRepository;
    }

    public function unlock(Personnage $character, $element, bool $byMeeting = false, ?\DateTimeInterface $at = null): void
    {
        $key = EntityRegistry::keyOf($element);

        if ($key === null || $element->getId() === null) {
            return;
        }

        $entry = $character->getId() . '|' . $key . '|' . $element->getId();

        if (isset($this->pending[$entry])) {
            return;
        }

        $existing = $this->unlockRepository->findOneBy([
            'character' => $character,
            'entity' => $key,
            'elementId' => $element->getId(),
        ]);

        if ($existing !== null) {
            return;
        }

        $this->pending[$entry] = true;

        $unlock = new Unlock();
        $unlock->setCharacter($character);
        $unlock->setEntity($key);
        $unlock->setElementId($element->getId());
        $unlock->setUnlockedAt($at ?? new \DateTime());
        $unlock->setByMeeting($byMeeting);

        $this->entityManager->persist($unlock);
    }

    public function syncAccess($element, ?\DateTimeInterface $at = null): void
    {
        $met = $this->playersWhoMet($element);

        if (empty($met)) {
            return;
        }

        if (Access::unlocksByMeeting($element)) {
            foreach ($met as $player) {
                $this->unlock($player, $element, true, $at);
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

        if ($element instanceof Objet) {
            return $this->participationRepository->findPlayersWhoFound($element);
        }

        return [];
    }

    public function syncScene(Scene $scene): void
    {
        $players = [];
        $encountered = [];

        foreach ($scene->getParticipations() as $participation) {
            $character = $participation->getPersonnage();

            if ($participation->getEstPj()) {
                $players[] = $character;
            }

            $encountered[] = $character;
        }

        if ($scene->getLieu() !== null) {
            $encountered[] = $scene->getLieu();
        }

        foreach ($scene->getFoundObjects() as $object) {
            $encountered[] = $object;
        }

        foreach ($players as $player) {
            foreach ($encountered as $element) {
                if ($element !== $player && Access::unlocksByMeeting($element)) {
                    $this->unlock($player, $element, true);
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

        $characters = [];
        foreach ($this->unlockRepository->findForElement($key, $element->getId()) as $unlock) {
            if (!$unlock->isByMeeting()) {
                $characters[] = $unlock->getCharacter();
            }
        }

        return $characters;
    }

    public function sync($element, $characters): void
    {
        $key = EntityRegistry::keyOf($element);

        if ($key === null || $element->getId() === null) {
            return;
        }

        $wanted = [];
        foreach ($characters as $character) {
            $wanted[$character->getId()] = $character;
        }

        foreach ($this->unlockRepository->findForElement($key, $element->getId()) as $unlock) {
            $id = $unlock->getCharacter()->getId();

            if (isset($wanted[$id])) {
                $unlock->setByMeeting(false);
                unset($wanted[$id]);
            } elseif (!$unlock->isByMeeting()) {
                $this->entityManager->remove($unlock);
            }
        }

        foreach ($wanted as $character) {
            $this->unlock($character, $element);
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
