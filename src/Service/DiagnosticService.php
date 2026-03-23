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
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Calcule le score total (LCU) à partir d'un tableau d'IDs de réponses.
     */
    public function calculateScore(array $responseIds): int
    {
        $score = 0;

        if (empty($responseIds)) {
            return $score;
        }

        // Assure que c'est un tableau de valeurs indexé
        $responses = $this->responseRepository->findBy(['id' => array_values($responseIds)]);

        foreach ($responses as $response) {
            $score += $response->getPoints();
        }

        return $score;
    }

    /**
     * Détermine le palier de stress pour un score donné ET un quiz donné.
     * Chaque quiz ayant ses propres paliers, on filtre par quiz.
     *
     * @param int  $score Score total calculé
     * @param Quiz $quiz  Le quiz passé par l'utilisateur
     *
     * @return StressThreshold|null Le palier correspondant, null si aucun configuré
     */
    public function getThresholdForScore(int $score, Quiz $quiz): ?StressThreshold
    {
        foreach ($quiz->getStressThresholds() as $threshold) {
            $max = $threshold->getMaxScore();
            if ($score >= $threshold->getMinScore() && (null === $max || $score <= $max)) {
                return $threshold;
            }
        }

        return null;
    }

    /**
     * Récupère un seuil par son ID (utilisé pour les utilisateurs non connectés).
     */
    public function getThresholdById(string $id): ?StressThreshold
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
