<?php
namespace App\Service;

use App\Entity\Participation;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ParticipationHandler
{
    private $repo;
    private $entity;
    private $sessionFlash;
    private $unlocker;

    public function __construct(PersonnageRepository $characterRepository, EntityManagerInterface $entityManagerInterface, RequestStack $requestStack, Unlocker $unlocker)
    {
        $this->repo = $characterRepository;
        $this->entity = $entityManagerInterface;
        $this->sessionFlash = $requestStack->getSession()->getFlashBag();
        $this->unlocker = $unlocker;
    }

    public function addParticipations($participantsToAdd, $scene)
    {
        if (!empty($participantsToAdd)) {
            foreach ($participantsToAdd as $key => $participant) {
                $character = $this->repo->find($participantsToAdd[$key]['id']);
                if ($character !== null) {
                    $newParticipation = new Participation;
                    $newParticipation->setScene($scene);
                    $newParticipation->setPersonnage($character);
                    $newParticipation->setXpGagne($participantsToAdd[$key]['xp']);
                    $newParticipation->setEstMort($participantsToAdd[$key]['mort']);
                    $newParticipation->setEstPj($participantsToAdd[$key]['estPj']);
                    $newParticipation->setXpBonus(!empty($participantsToAdd[$key]['xpBonus']));
                    $this->entity->persist($newParticipation);

                    $this->sessionFlash->add('success', 'Le personnage ' . $character->getPrenom() . ' a bien été ajouté en participant !');
                } else {
                    $this->sessionFlash->add('danger', 'Le personnage n\'a pu être ajouté en participant !');
                }
            }
        }
    }

    public function mergeParticipants($pcIds, $pcXp, $pcDead, $npcIds, $npcDead, $pcXpBonus = null) {

        if(!empty($pcIds)) {
            if(empty($pcDead))
                $pcDead = [];
            if(empty($pcXpBonus))
                $pcXpBonus = [];
        } else {
            $pcIds = [];
            $pcXp = [];
            $pcDead = [];
            $pcXpBonus = [];
        }

        if(!empty($npcIds)) {
            if(empty($npcDead))
                $npcDead = [];
        } else {
            $npcIds = [];
            $npcDead = [];
        }

        $participantsToAdd = [];
        $index = 0;

        if(!empty($pcIds)) {
            foreach($pcIds as $key => $pcId) {
                $participantsToAdd[$index]['id'] = $pcIds[$key];
                $participantsToAdd[$index]['xp'] = $pcXp[$key];
                if (!empty($pcDead[$key]))
                    $participantsToAdd[$index]['mort'] = 1;
                else
                    $participantsToAdd[$index]['mort'] = 0;
                $participantsToAdd[$index]['xpBonus'] = !empty($pcXpBonus[$key]) ? 1 : 0;
                $participantsToAdd[$index]['estPj'] = 1;
                $index++;
            }
        }

        if(!empty($npcIds)) {
            foreach($npcIds as $key => $npcId) {
                $participantsToAdd[$index]['id'] = $npcIds[$key];
                $participantsToAdd[$index]['xp'] = 0;
                if (!empty($npcDead[$key]))
                    $participantsToAdd[$index]['mort'] = 1;
                else
                    $participantsToAdd[$index]['mort'] = 0;
                $participantsToAdd[$index]['xpBonus'] = 0;
                $participantsToAdd[$index]['estPj'] = 0;
                $index++;
            }
        }

        return $participantsToAdd;
    }
}