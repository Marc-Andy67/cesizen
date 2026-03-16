<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/question', name: 'app_admin_question_')]
#[IsGranted('ROLE_ADMIN')]
class QuestionController extends AbstractCrudController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request, 
        QuestionRepository $questionRepository,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $questionRepository->createQueryBuilder('q')
            ->orderBy('q.title', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('admin/question/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $question = new Question();
        $question->setIsActive(true);

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();
            $this->addSuccessFlash('Question créée avec succès.');
            return $this->redirectToRoute('app_admin_question_index');
        }

        return $this->render('admin/question/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/editer', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Question $question, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addSuccessFlash('Question modifiée avec succès.');
            return $this->redirectToRoute('app_admin_question_index');
        }

        return $this->render('admin/question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }
}
