<?php

namespace App\Strategy;

class ModerateStressStrategy implements StressLevelStrategyInterface
{
    public function supports(int $score): bool
    {
        return $score >= 150 && $score < 300;
    }

    public function getLevelName(): string
    {
        return 'Modéré';
    }

    public function getRecommendation(): string
    {
        return 'Vous présentez un niveau de stress modéré. Envisagez d\'intégrer des moments de relaxation réguliers dans votre quotidien.';
    }
}
