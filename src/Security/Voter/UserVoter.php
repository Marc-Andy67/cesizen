<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * UserVoter — Gère les autorisations sur l'entité User.
 * 
 * Ce Voter implémente les règles métier pour savoir qui peut voir, modifier ou supprimer un utilisateur :
 * - Un administrateur (ROLE_ADMIN) a tous les droits
 * - Un utilisateur peut voir, modifier ou supprimer son propre compte
 */
class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // On ne vote que sur les attributs définis et si le sujet est un User
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas connecté, l'accès est refusé
        if (!$user instanceof User) {
            return false;
        }

        // Si l'utilisateur est administrateur, on autorise tout
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Pour les droits classiques, on vérifie que l'utilisateur connecté est le même que le sujet
        /** @var User $subject */
        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canView(User $subject, User $user): bool
    {
        // Un utilisateur peut voir son propre profil
        return $user === $subject;
    }

    private function canEdit(User $subject, User $user): bool
    {
        // Un utilisateur peut modifier son propre profil
        return $user === $subject;
    }

    private function canDelete(User $subject, User $user): bool
    {
        // Un utilisateur peut demander la suppression de son propre profil
        return $user === $subject;
    }
}
