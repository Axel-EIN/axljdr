<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UtilisateurChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Utilisateur && !$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte n\'est pas encore activé. Cliquez sur le lien de confirmation reçu par e-mail, ou contactez un administrateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
