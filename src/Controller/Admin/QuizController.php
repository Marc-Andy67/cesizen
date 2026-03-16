<?php

namespace App\Controller\Admin;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/quiz', name: 'app_admin_quiz_')]
#[IsGranted('ROLE_ADMIN')]
class QuizController extends AbstractCrudController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request, 
        QuizRepository $quizRepository,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $quizRepository->createQueryBuilder('q')
            ->orderBy('q.title', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/quiz/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        $quiz->setIsActive(false);

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();
            $this->addSuccessFlash('Quiz créé avec succès.');
            return $this->redirectToRoute('app_admin_quiz_index');
        }

        return $this->render('admin/quiz/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/editer', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addSuccessFlash('Quiz modifié avec succès.');
            return $this->redirectToRoute('app_admin_quiz_index');
        }

        return $this->render('admin/quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }
}
