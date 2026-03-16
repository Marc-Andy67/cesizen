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
     * @return bool
     */
    public function supports(int $score): bool;

    /**
     * Retourne le nom du niveau de stress.
     * 
     * @return string
     */
    public function getLevelName(): string;

    /**
     * Retourne la recommandation de base associée à ce niveau.
     * 
     * @return string
     */
    public function getRecommendation(): string;
}
