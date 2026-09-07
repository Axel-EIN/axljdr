<?php

namespace App\Controller;

use App\Service\Visibility;
use Symfony\Component\HttpFoundation\Response;

trait VisibilityTrait
{
    private function accessGuard(Visibility $visibility, object $element, string $entity, string $category): ?Response
    {
        if ($visibility->isReadable($element)) {
            return null;
        }

        if (!$visibility->isListed($element)) {
            return $this->render('element-hidden.html.twig', [], new Response('', Response::HTTP_NOT_FOUND));
        }

        return $this->render('element-locked.html.twig', [
            'entity' => $entity,
            'category' => $category,
        ]);
    }
}
