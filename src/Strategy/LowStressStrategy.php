<?php

namespace App\Strategy;

class LowStressStrategy implements StressLevelStrategyInterface
{
    public function supports(int $score): bool
    {
        return $score < 150;
    }

    public function getLevelName(): string
    {
        return 'Faible';
    }

    public function getRecommendation(): string
    {
        return 'Votre niveau de stress est faible. Continuez à maintenir de bonnes habitudes de vie.';
    }
}
