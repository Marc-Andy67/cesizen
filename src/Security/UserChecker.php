<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserChecker — Vérifie le statut du compte avant et après l'authentification.
 *
 * Symfony appelle automatiquement ce checker à deux moments :
 * - checkPreAuth()  : AVANT la vérification du mot de passe
 * - checkPostAuth() : APRÈS la vérification du mot de passe
 *
 * Règles métier vérifiées :
 * - isActive = false  → compte désactivé par un admin → accès refusé
 * - lockedUntil > now → compte verrouillé brute force → accès refusé avec délai
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Vérifie le compte AVANT la vérification du mot de passe.
     *
     * On vérifie ici plutôt qu'en post-auth pour éviter de hasher
     * le mot de passe inutilement sur un compte bloqué (performance + sécurité).
     *
     * @throws CustomUserMessageAccountStatusException si compte inactif ou verrouillé
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérifie si le compte a été désactivé manuellement par un administrateur
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé. Veuillez contacter le support.');
        }

        // Vérifie si le compte est temporairement verrouillé (protection brute force)
        // lockedUntil est défini par LoginFailureSubscriber après 5 tentatives échouées
        $now = new \DateTimeImmutable();
        if (null !== $user->getLockedUntil() && $now < $user->getLockedUntil()) {
            $lockedUntil = $user->getLockedUntil()->setTimezone(new \DateTimeZone('Europe/Paris'));
            $formattedDate = $lockedUntil->format('d/m/Y à H:i:s');
            throw new CustomUserMessageAccountStatusException(sprintf('Votre compte est verrouillé suite à de multiples tentatives échouées. Réessayez après le %s.', $formattedDate));
        }
    }

    /**
     * Vérifie le compte APRÈS l'authentification réussie.
     *
     * Note Symfony 8 : le paramètre $token est obligatoire dans la signature
     * même s'il est optionnel (nullable) — sans lui PHP lève une FatalError.
     *
     * La réinitialisation de failedAttempt et lockedUntil est gérée
     * dans LoginSuccessSubscriber pour respecter le principe de responsabilité unique.
     */
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // Aucune vérification post-auth nécessaire ici.
        // Voir LoginSuccessSubscriber pour la réinitialisation des tentatives.
    }
}
