<?php

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\Quiz;
use App\Entity\StressThreshold;
use App\Entity\User;
use App\Repository\ResponseRepository;
use App\Repository\StressThresholdRepository;
use Doctrine\ORM\EntityManagerInterface;

class DiagnosticService
{
    public function __construct(
        private readonly ResponseRepository $responseRepository,
        private readonly StressThresholdRepository $thresholdRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Calcule le score total (LCU) à partir d'un tableau d'IDs de réponses.
     *
     * @param array $responseIds
     * @return int
     */
    public function calculateScore(array $responseIds): int
    {
        $score = 0;
        
        if (empty($responseIds)) {
            return $score;
        }

        $responses = $this->responseRepository->findBy(['id' => $responseIds]);
        
        foreach ($responses as $response) {
            $score += $response->getPoints();
        }

        return $score;
    }

    /**
     * Détermine le seuil de stress correspondant à un score.
     *
     * @param int $score
     * @return StressThreshold|null
     */
    public function getThresholdForScore(int $score): ?StressThreshold
    {
        // On récupère le seuil où le score est entre minScore et maxScore (ou juste >= minScore si maxScore est null)
        $thresholds = $this->thresholdRepository->findBy([], ['minScore' => 'ASC']);
        
        foreach ($thresholds as $threshold) {
            $min = $threshold->getMinScore();
            $max = $threshold->getMaxScore();

            if ($score >= $min && ($max === null || $score < $max)) {
                return $threshold;
            }
        }

        // Si aucun seuil ne correspond (ne devrait pas arriver avec une bonne conf)
        return null;
    }

    /**
     * Récupère un seuil par son ID (utilisé pour les utilisateurs non connectés).
     */
    public function getThresholdById(int $id): ?StressThreshold
    {
        return $this->thresholdRepository->find($id);
    }

    /**
     * Sauvegarde le résultat (Assessment) d'un utilisateur.
     */
    public function saveAssessment(User $user, Quiz $quiz, array $responseIds, int $score): Assessment
    {
        $assessment = new Assessment();
        $assessment->setOwner($user);
        $assessment->setQuiz($quiz);
        $assessment->setDate(new \DateTimeImmutable());
        $assessment->setTotalScore($score);

        $responses = $this->responseRepository->findBy(['id' => $responseIds]);
        foreach ($responses as $response) {
            $assessment->addResponse($response);
        }

        $this->em->persist($assessment);

        return $assessment;
    }
}
