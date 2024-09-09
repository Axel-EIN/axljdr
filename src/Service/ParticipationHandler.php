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

    public function __construct(PersonnageRepository $personnageRepository, EntityManagerInterface $entityManagerInterface, RequestStack $requestStack )
    {
        $this->repo = $personnageRepository;
        $this->entity = $entityManagerInterface;
        $this->sessionFlash = $requestStack->getSession()->getFlashBag();
    }

    public function ajouterParticipations($participants_a_ajoutes, $scene)
    {
        if (!empty($participants_a_ajoutes)) {
            foreach ($participants_a_ajoutes as $cle => $un_participant_a_ajoute) {
                if ( !empty($this->repo->find($participants_a_ajoutes[$cle]['id'])) ) {
                    $personnage = $this->repo->find($participants_a_ajoutes[$cle]['id']);
                    $nouvelle_participation = new Participation;
                    $nouvelle_participation->setScene($scene);
                    $nouvelle_participation->setPersonnage($personnage);
                    $nouvelle_participation->setXpGagne($participants_a_ajoutes[$cle]['xp']);
                    $nouvelle_participation->setEstMort($participants_a_ajoutes[$cle]['mort']);
                    $nouvelle_participation->setEstPj($participants_a_ajoutes[$cle]['estPj']);
                    $this->entity->persist($nouvelle_participation);
                    $this->sessionFlash->add('success', 'Le personnage ' . $personnage->getPrenom() . ' a bien été ajouté en participant !');
                } else {
                    $this->sessionFlash->add('danger', 'Le personnage n\'a pu être ajouté en participant !');
                }
            }
        }
    }

    public function fusionnerParticipants($participants_pjs, $participants_pjs_xp, $participants_pjs_mort, $participants_pnjs, $participants_pnjs_mort) {

        if(!empty($participants_pjs)) {
            if(empty($participants_pjs_mort))
                $participants_pjs_mort = [];
        } else {
            $participants_pjs = [];
            $participants_pjs_xp = [];
            $participants_pjs_mort = [];
        }

        if(!empty($participants_pnjs)) {
            if(empty($participants_pnjs_mort))
                $participants_pnjs_mort = [];
        } else {
            $participants_pnjs = [];
            $participants_pnjs_mort = [];
        }

        $participants_a_ajoutes = [];
        $compteur = 0;
        
        if(!empty($participants_pjs)) {
            foreach($participants_pjs as $key => $un_participant_pj) {
                $participants_a_ajoutes[$compteur]['id'] = $participants_pjs[$key];
                $participants_a_ajoutes[$compteur]['xp'] = $participants_pjs_xp[$key];
                if (!empty($participants_pjs_mort[$key]))
                    $participants_a_ajoutes[$compteur]['mort'] = 1;
                else
                    $participants_a_ajoutes[$compteur]['mort'] = 0;
                $participants_a_ajoutes[$compteur]['estPj'] = 1;
                $compteur++;
            }
        }
        
        if(!empty($participants_pnjs)) {
            foreach($participants_pnjs as $cle => $un_participant_pnj) {
                $participants_a_ajoutes[$compteur]['id'] = $participants_pnjs[$cle];
                $participants_a_ajoutes[$compteur]['xp'] = 0;
                if (!empty($participants_pnjs_mort[$cle]))
                    $participants_a_ajoutes[$compteur]['mort'] = 1;
                else
                    $participants_a_ajoutes[$compteur]['mort'] = 0;
                $participants_a_ajoutes[$compteur]['estPj'] = 0;
                $compteur++;
            }
        }

        return $participants_a_ajoutes;
    }
}