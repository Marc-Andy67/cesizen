<?php

namespace App\Controller\Admin;

use App\Repository\QuizRepository;
use App\Repository\StressThresholdRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/seuil-stress', name: 'app_admin_stress_threshold_')]
#[IsGranted('ROLE_ADMIN')]
class StressThresholdController extends AbstractCrudController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        StressThresholdRepository $repository,
        QuizRepository $quizRepository,
        PaginatorInterface $paginator,
    ): Response {
        $queryBuilder = $repository->createQueryBuilder('s')
            ->leftJoin('s.quiz', 'q')
            ->addSelect('q')
            ->orderBy('q.title', 'ASC')
            ->addOrderBy('s.minScore', 'ASC');

        $quizId = $request->query->get('quiz_id');
        if ($quizId) {
            $queryBuilder->andWhere('s.quiz = :quizId')
                         ->setParameter('quizId', $quizId);
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/stress_threshold/index.html.twig', [
            'pagination' => $pagination,
            'quizzes' => $quizRepository->findAll(),
            'current_quiz_id' => $quizId,
        ]);
    }
}
