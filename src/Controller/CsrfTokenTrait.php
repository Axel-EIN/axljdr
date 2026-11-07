<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

trait CsrfTokenTrait
{
    private function validToken(Request $request, string $id): bool
    {
        if ($this->isCsrfTokenValid($id, $request->request->get('_csrf_token'))) {
            return true;
        }

        $this->addFlash('danger', 'La page a expiré : rechargez-la et réessayez.');

        return false;
    }
}
