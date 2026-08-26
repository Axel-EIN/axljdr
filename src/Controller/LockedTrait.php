<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

trait LockedTrait
{
    private function lockedPage(object $element, string $entity, string $category): ?Response
    {
        if (!$element->getLocked() || $this->isGranted('ROLE_MJ')) {
            return null;
        }

        return $this->render('element-locked.html.twig', [
            'entity' => $entity,
            'category' => $category,
        ]);
    }
}
