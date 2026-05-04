<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Numeroteur
{
    private $em;

    public function __construct(EntityManagerInterface $EntityManagerInterface)
    {
        $this->em = $EntityManagerInterface;
    }

    public function reordonnerNumero(int $initialEntityId ,int $positionDepart, int $positionArrivee, array $fratrieDepart, array $fratrieArrivee)
    {
        if ($positionDepart < 0 || $fratrieDepart != $fratrieArrivee)
        {
            foreach($fratrieArrivee as $uneEntite) {
                $uneEntiteNumero = $uneEntite->getNumero();
                if ($uneEntite->getId() != $initialEntityId && $uneEntiteNumero >= $positionArrivee) {
                    $uneEntite->setNumero($uneEntiteNumero+1);
                    $this->em->persist($uneEntite);
                }
            }
        }
    
        if($positionArrivee < 0 || $fratrieDepart != $fratrieArrivee)
        {
            foreach($fratrieDepart as $uneEntite) {
                $uneEntiteNumero = $uneEntite->getNumero();
                if ($uneEntite->getId() != $initialEntityId && $uneEntiteNumero > $positionDepart) {
                    $uneEntite->setNumero($uneEntiteNumero-1);
                    $this->em->persist($uneEntite);
                }
            }
        }
    
        if ($fratrieDepart == $fratrieArrivee)
        {
            if ($positionArrivee < $positionDepart) {
                foreach($fratrieDepart as $uneEntite) {
                    $uneEntiteNumero = $uneEntite->getNumero();
                    if ($uneEntite->getId() != $initialEntityId && $uneEntiteNumero >= $positionArrivee && $uneEntiteNumero < $positionDepart) {
                        $uneEntite->setNumero($uneEntiteNumero+1);
                        $this->em->persist($uneEntite);
                    }
                }
            } elseif ($positionArrivee > $positionDepart) {
                foreach($fratrieDepart as $uneEntite) {
                    $uneEntiteNumero = $uneEntite->getNumero();
                    if ($uneEntite->getId() != $initialEntityId && $uneEntiteNumero <= $positionArrivee && $uneEntiteNumero > $positionDepart) {
                        $uneEntite->setNumero($uneEntiteNumero-1);
                        $this->em->persist($uneEntite);
                    }
                }
            }
        }

        $this->em->flush();
    }
}