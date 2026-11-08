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
        $wasPlaced = $previousPosition >= 0;
        $isPlaced = $newPosition >= 0;

        if ($wasPlaced && $isPlaced && $this->sameGroup($previousSiblings, $newSiblings)) {
            if ($newPosition < $previousPosition)
                $this->shift($previousSiblings, $initialEntityId, $newPosition, $previousPosition - 1, 1);
            elseif ($newPosition > $previousPosition)
                $this->shift($previousSiblings, $initialEntityId, $previousPosition + 1, $newPosition, -1);
        } else {
            if ($wasPlaced)
                $this->shift($previousSiblings, $initialEntityId, $previousPosition + 1, PHP_INT_MAX, -1);
            if ($isPlaced)
                $this->shift($newSiblings, $initialEntityId, $newPosition, PHP_INT_MAX, 1);
        }

        $this->em->flush();
    }

    private function sameGroup(array $previousSiblings, array $newSiblings): bool
    {
        return $this->ids($previousSiblings) == $this->ids($newSiblings);
    }

    private function ids(array $siblings): array
    {
        $ids = array_map(fn ($sibling) => $sibling->getId(), $siblings);
        sort($ids);

        return $ids;
    }

    private function shift(array $siblings, int $excludedId, int $from, int $to, int $step): void
    {
        foreach ($siblings as $sibling) {
            $number = $sibling->getNumero();
            if ($sibling->getId() != $excludedId && $number !== null && $number >= $from && $number <= $to) {
                $sibling->setNumero($number + $step);
                $this->em->persist($sibling);
            }
        }
    }
}
