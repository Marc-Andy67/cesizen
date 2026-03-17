<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * RgpdService — Service métier pour la conformité RGPD.
 *
 * Ce service implémente les fonctionnalités obligatoires pour le RGPD :
 * - Droit d'accès : Exportation des données personnelles au format JSON
 * - Droit à l'oubli : Anonymisation du compte utilisateur
 */
class RgpdService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Exporte les données personnelles d'un utilisateur au format JSON.
     *
     * @param User $user Utilisateur concerné
     *
     * @return array Tableau associatif des données personnelles
     */
    public function exportUserData(User $user): array
    {
        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'roles' => $user->getRoles(),
            'creationDate' => $user->getCreationDate()?->format(\DateTimeInterface::ATOM),
            'lastConnection' => $user->getLastConnection()?->format(\DateTimeInterface::ATOM),
            'isActive' => $user->isActive(),
            // Les assessments (historique de diagnostics) ne contiennent pas de PII directes
            // en dehors de leur lien avec le compte, mais on pourrait les exporter ici si désiré.
        ];
    }

    /**
     * Anonymise un utilisateur suite à une demande de suppression (Droit à l'oubli).
     *
     * Plutôt que de supprimer la ligne en base (qui pourrait casser des clés étrangères
     * ou fausser des statistiques globales de diagnostics anonymisés), on écrase les PII.
     *
     * @param User $user Utilisateur à anonymiser
     */
    public function anonymizeUser(User $user): void
    {
        // Génère un UUID aléatoire pour éviter toute collision sur l'email unique
        $randomUuid = Uuid::v4();

        $user->setEmail(sprintf('anonymized_%s@cesizen.local', $randomUuid));
        $user->setName('Utilisateur Anonyme');

        // On rend le mot de passe inutilisable
        $user->setPassword(password_hash(random_bytes(32), PASSWORD_ARGON2I));

        // On désactive le compte
        $user->setIsActive(false);

        $this->entityManager->flush();
    }
}
