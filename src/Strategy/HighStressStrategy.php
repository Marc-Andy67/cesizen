<?php

namespace App\Strategy;

class HighStressStrategy implements StressLevelStrategyInterface
{
    public function supports(int $score): bool
    {
        return $score >= 300 && $score < 450;
    }

    public function getLevelName(): string
    {
        return 'Élevé';
    }

    public function getRecommendation(): string
    {
        return 'Votre niveau de stress est élevé. Il y a un risque significatif de problèmes de santé. Nous vous recommandons d\'en discuter avec un professionnel de santé.';
    }
}
