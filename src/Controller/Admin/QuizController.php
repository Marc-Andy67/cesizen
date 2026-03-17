<?php

namespace App\Controller\Admin;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/quiz')]
#[IsGranted('ROLE_ADMIN')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_admin_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        return $this->render('admin/quiz/index.html.twig', [
            'quizzes' => $quizRepository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation métier avant d'activer un quiz
            if ($quiz->isActive() && !$this->canBeActivated($quiz)) {
                $quiz->setIsActive(false);
                $this->addFlash('warning', 'Le questionnaire a été créé mais est désactivé. Il doit contenir au moins 1 question active et chaque question doit avoir au moins 2 réponses actives.');
            } else {
                $this->addFlash('success', 'Questionnaire créé avec succès.');
            }

            $entityManager->persist($quiz);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/quiz/form.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($quiz->isActive() && !$this->canBeActivated($quiz)) {
                $quiz->setIsActive(false);
                $this->addFlash('warning', 'Modifications enregistrées. Le questionnaire a été désactivé : vérifiez qu\'il a bien 1 question active avec au moins 2 réponses actives chacune.');
            } else {
                $this->addFlash('success', 'Questionnaire modifié avec succès.');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/quiz/form.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_quiz_toggle', methods: ['POST'])]
    public function toggle(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$quiz->getId(), $request->request->get('_token'))) {
            // Si on tente de l'activer, on vérifie les règles métier
            if (!$quiz->isActive()) {
                if (!$this->canBeActivated($quiz)) {
                    $this->addFlash('error', 'Impossible d\'activer ce questionnaire : il doit avoir au moins une question active, et chaque question active doit avoir au moins 2 réponses actives.');

                    return $this->redirectToRoute('app_admin_quiz_index');
                }
            }

            $quiz->setIsActive(!$quiz->isActive());
            $entityManager->flush();
            $this->addFlash('success', 'Statut du questionnaire mis à jour.');
        }

        return $this->redirectToRoute('app_admin_quiz_index');
    }

    #[Route('/{id}/supprimer', name: 'app_admin_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
            if (!$quiz->getAssessments()->isEmpty()) {
                $this->addFlash('error', 'Impossible de supprimer ce questionnaire car des évaluations (assessments) y sont déjà rattachées.');

                return $this->redirectToRoute('app_admin_quiz_index');
            }

            $entityManager->remove($quiz);
            $entityManager->flush();
            $this->addFlash('success', 'Questionnaire supprimé.');
        }

        return $this->redirectToRoute('app_admin_quiz_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Vérifie si le quiz respecte les règles pour être actif (1 question min, 2 réponses min par question).
     */
    private function canBeActivated(Quiz $quiz): bool
    {
        $activeQuestions = 0;
        foreach ($quiz->getQuestions() as $question) {
            if ($question->isActive()) {
                ++$activeQuestions;
                $activeResponsesCount = 0;
                foreach ($question->getResponses() as $response) {
                    if ($response->isActive()) {
                        ++$activeResponsesCount;
                    }
                }
                if ($activeResponsesCount < 2) {
                    return false;
                }
            }
        }

        return $activeQuestions > 0;
    }
}
