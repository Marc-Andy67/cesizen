<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * AbstractCrudController — Classe parente pour les contrôleurs d'administration (Pattern Template Method).
 *
 * Centralise les comportements communs (récupération de l'Entity Manager, vérifications des droits)
 * pour éviter la duplication de code dans les controllers de l'espace admin.
 */
abstract class AbstractCrudController extends AbstractController
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * S'assure que l'utilisateur a bien le rôle administrateur.
     */
    protected function denyAccessUnlessAdmin(): void
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès refusé - Réservé aux administrateurs.');
    }

    /**
     * Gère les messages flash de succès.
     */
    protected function addSuccessFlash(string $message): void
    {
        $this->addFlash('success', $message);
    }

    /**
     * Gère les messages flash d'erreur.
     */
    protected function addErrorFlash(string $message): void
    {
        $this->addFlash('error', $message);
    }
}
