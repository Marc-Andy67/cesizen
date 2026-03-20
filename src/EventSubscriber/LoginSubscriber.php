<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * LoginSubscriber — Gère les événements de connexion réussie ou échouée.
 *
 * Cette classe implémente le pattern Observer via le système d'événements de Symfony.
 * Elle permet une protection contre les attaques par force brute (Brute Force Protection) :
 * - Incrémente le compteur de tentatives échouées sur une `LoginFailureEvent`
 * - Verrouille le compte pendant 15 minutes si le nombre de tentatives dépasse 5
 * - Réinitialise le compteur sur une `LoginSuccessEvent`
 */
class LoginSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport) {
            return;
        }

        try {
            $user = $passport->getUser();
        } catch (\LogicException) {
            // Throttling actif — pas de user accessible, on ignore
            return;
        }

        if (!$user instanceof User) {
            return;
        }

        $now = new \DateTimeImmutable();
        if (null !== $user->getLockedUntil() && $now < $user->getLockedUntil()) {
            return; // déjà verrouillé, on ne fait rien
        }

        // Incrémente le nombre de tentatives échouées
        $failedAttempts = $user->getFailedAttempt() ?? 0;
        ++$failedAttempts;
        $user->setFailedAttempt($failedAttempts);

        // Si on atteint 5 tentatives échouées, on verrouille le compte pour 1 jour
        if ($failedAttempts >= 5) {
            $user->setLockedUntil(new \DateTimeImmutable('+1 day'));
        }

        $this->entityManager->flush();
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Réinitialise les tentatives échouées et met à jour la date de dernière connexion
        $user->setFailedAttempt(0);
        $user->setLockedUntil(null);
        $user->setLastConnection(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
