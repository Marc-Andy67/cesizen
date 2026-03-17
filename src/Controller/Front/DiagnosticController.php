<?php

namespace App\Controller\Front;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use App\Service\DiagnosticService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/diagnostic', name: 'app_diagnostic_')]
class DiagnosticController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findBy(['isActive' => true]);

        return $this->render('front/diagnostic/index.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/{id}/passer', name: 'take', methods: ['GET', 'POST'])]
    public function take(Quiz $quiz, Request $request, DiagnosticService $diagnosticService, EntityManagerInterface $em): Response
    {
        if (!$quiz->isActive()) {
            throw $this->createNotFoundException('Ce test n\'est plus disponible.');
        }

        if ($request->isMethod('POST')) {
            // Le formulaire soumet un tableau de réponses (ID de la réponse pour chaque question)
            $submittedResponses = $request->request->all('responses');

            if (empty($submittedResponses)) {
                $this->addFlash('error', 'Veuillez répondre à au moins une question ou confirmer que vous n\'avez vécu aucun de ces événements.');

                return $this->redirectToRoute('app_diagnostic_take', ['id' => $quiz->getId()]);
            }

            // Calcul du score via le service
            $score = $diagnosticService->calculateScore($submittedResponses);
            $threshold = $diagnosticService->getThresholdForScore($score, $quiz);

            // Si l'utilisateur est connecté, on sauvegarde son résultat (Assessment complet)
            if ($this->getUser()) {
                /** @var \App\Entity\User $user */
                $user = $this->getUser();
                $assessment = $diagnosticService->saveAssessment($user, $quiz, $submittedResponses, $score);
                $em->flush();

                return $this->redirectToRoute('app_diagnostic_result', ['id' => $assessment->getId()]);
            }

            // Si utilisateur anonyme, on stoque le résultat en session
            $request->getSession()->set('temp_diagnostic_result', [
                'score' => $score,
                'quiz_id' => $quiz->getId(),
                'quiz_title' => $quiz->getTitle(),
                'threshold_id' => $threshold ? $threshold->getId() : null,
                'date' => new \DateTimeImmutable(),
            ]);

            return $this->redirectToRoute('app_diagnostic_result_anonymous');
        }

        // On trie les questions pour l'affichage (si besoin, sinon tri par défaut de Doctrine ou paramétré dans l'entité)
        $questions = $quiz->getQuestions()->toArray();
        // Optionnel : trier les questions par un ordre défini (ici on laisse l'ordre d'insertion)

        return $this->render('front/diagnostic/take.html.twig', [
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

    #[Route('/resultat/anonyme', name: 'result_anonymous', methods: ['GET'])]
    public function resultAnonymous(Request $request, DiagnosticService $diagnosticService): Response
    {
        $result = $request->getSession()->get('temp_diagnostic_result');

        if (!$result) {
            return $this->redirectToRoute('app_diagnostic_index');
        }

        $threshold = null;
        if ($result['threshold_id']) {
            $threshold = $diagnosticService->getThresholdById($result['threshold_id']);
        }

        return $this->render('front/diagnostic/result.html.twig', [
            'score' => $result['score'],
            'quiz_title' => $result['quiz_title'],
            'threshold' => $threshold,
            'date' => $result['date'],
            'is_anonymous' => true,
        ]);
    }

    #[Route('/resultat/{id}', name: 'result', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function result(\App\Entity\Assessment $assessment, DiagnosticService $diagnosticService): Response
    {
        // Vérifier que l'assessment appartient bien à l'utilisateur courant, sauf si admin (à ajouter potentiellement)
        if ($assessment->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas voir ce résultat.');
        }

        $threshold = $diagnosticService->getThresholdForScore($assessment->getTotalScore(), $assessment->getQuiz());

        return $this->render('front/diagnostic/result.html.twig', [
            'assessment' => $assessment,
            'score' => $assessment->getTotalScore(),
            'quiz_title' => $assessment->getQuiz()->getTitle(),
            'threshold' => $threshold,
            'date' => $assessment->getDate(),
            'is_anonymous' => false,
        ]);
    }
}
