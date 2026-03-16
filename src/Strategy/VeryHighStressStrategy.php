<?php

namespace App\Strategy;

class VeryHighStressStrategy implements StressLevelStrategyInterface
{
    public function supports(int $score): bool
    {
        return $score >= 450;
    }

    public function getLevelName(): string
    {
        return 'Très élevé';
    }

    public function getRecommendation(): string
    {
        return 'Votre niveau de stress est très élevé. Vous devriez envisager de consulter un professionnel de santé mentale dans les plus brefs délais.';
    }
}
