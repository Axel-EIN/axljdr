<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Refuse la connexion tant que l'adresse e-mail n'a pas été confirmée. Sans ce
 * checker, isVerified n'est lu que pour l'affichage et le lien de confirmation
 * ne sert à rien.
 */
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
