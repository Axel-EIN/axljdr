<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PlayerModeVoter implements VoterInterface
{
    private const GAME_MASTER_ROLES = ['ROLE_MJ', 'ROLE_ADMIN'];

    private $inner;

    public function __construct(VoterInterface $inner)
    {
        $this->inner = $inner;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $user = $token->getUser();

        if ($user instanceof Utilisateur && $user->isPlayerMode() && array_intersect(self::GAME_MASTER_ROLES, $attributes) !== []) {
            return self::ACCESS_DENIED;
        }

        return $this->inner->vote($token, $subject, $attributes);
    }
}
