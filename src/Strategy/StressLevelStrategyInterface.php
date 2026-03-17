<?php

namespace App\Strategy;

/**
 * Interface pour le calcul du niveau de stress et ses recommandations.
 * Fait partie du Behavioral Pattern 'Strategy'.
 */
interface StressLevelStrategyInterface
{
    /**
     * Détermine si cette stratégie s'applique au score donné.
     *
     * @param int $score Le résultat en points du diagnostic
     */
    public function supports(int $score): bool;

    /**
     * Retourne le nom du niveau de stress.
     */
    public function getLevelName(): string;

    /**
     * Retourne la recommandation de base associée à ce niveau.
     */
    public function getRecommendation(): string;
}
