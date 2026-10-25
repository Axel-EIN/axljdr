<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Orderer
{
    private $em;

    public function __construct(EntityManagerInterface $EntityManagerInterface)
    {
        $this->em = $EntityManagerInterface;
    }

    public function reorderNumber(int $initialEntityId ,int $previousPosition, int $newPosition, array $previousSiblings, array $newSiblings)
    {
        if ($previousPosition < 0 || $previousSiblings != $newSiblings)
        {
            foreach($newSiblings as $sibling) {
                $siblingNumber = $sibling->getNumero();
                if ($sibling->getId() != $initialEntityId && $siblingNumber >= $newPosition) {
                    $sibling->setNumero($siblingNumber+1);
                    $this->em->persist($sibling);
                }
            }
        }
    
        if($newPosition < 0 || $previousSiblings != $newSiblings)
        {
            foreach($previousSiblings as $sibling) {
                $siblingNumber = $sibling->getNumero();
                if ($sibling->getId() != $initialEntityId && $siblingNumber > $previousPosition) {
                    $sibling->setNumero($siblingNumber-1);
                    $this->em->persist($sibling);
                }
            }
        }
    
        if ($previousSiblings == $newSiblings)
        {
            if ($newPosition < $previousPosition) {
                foreach($previousSiblings as $sibling) {
                    $siblingNumber = $sibling->getNumero();
                    if ($sibling->getId() != $initialEntityId && $siblingNumber >= $newPosition && $siblingNumber < $previousPosition) {
                        $sibling->setNumero($siblingNumber+1);
                        $this->em->persist($sibling);
                    }
                }
            } elseif ($newPosition > $previousPosition) {
                foreach($previousSiblings as $sibling) {
                    $siblingNumber = $sibling->getNumero();
                    if ($sibling->getId() != $initialEntityId && $siblingNumber <= $newPosition && $siblingNumber > $previousPosition) {
                        $sibling->setNumero($siblingNumber-1);
                        $this->em->persist($sibling);
                    }
                }
            }
        }

        $this->em->flush();
    }
}
