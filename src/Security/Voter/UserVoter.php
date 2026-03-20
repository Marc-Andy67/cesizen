<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends Voter<string, User>
 */
class UserVoter extends Voter
{
    public const EDIT = 'USER_EDIT';
    public const TOGGLE_ACTIVE = 'USER_TOGGLE_ACTIVE';
    public const TOGGLE_ROLE = 'USER_TOGGLE_ROLE';
    public const DELETE = 'USER_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::TOGGLE_ACTIVE, self::TOGGLE_ROLE, self::DELETE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $currentUser = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $subjectUser */
        $subjectUser = $subject;

        // Seul un admin (via IsGranted ou vérification globale) accède à ce contrôleur,
        // donc on vérifie simplement les règles logiques ici.

        switch ($attribute) {
            case self::EDIT:
                return true;

            case self::TOGGLE_ACTIVE:
                // L'admin ne peut pas désactiver un autre admin
                if (in_array('ROLE_ADMIN', $subjectUser->getRoles(), true)) {
                    return false;
                }

                return true;

            case self::TOGGLE_ROLE:
                if (in_array('ROLE_ADMIN', $subjectUser->getRoles(), true)) {
                    if ($currentUser === $subjectUser) {
                        return true;
                    }

                    return false;
                }

                return true;

            case self::DELETE:
                // INTERDIT sur un ROLE_ADMIN
                if (in_array('ROLE_ADMIN', $subjectUser->getRoles(), true)) {
                    throw new AccessDeniedException('Cannot delete an admin user.');
                }

                return true;
        }

        return false;
    }
}
