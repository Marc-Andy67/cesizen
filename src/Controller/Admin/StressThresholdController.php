<?php

namespace App\Controller\Admin;

use App\Entity\StressThreshold;
use App\Form\Admin\StressThresholdType;
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
            ->leftJoin('s.quizzes', 'q')
            ->addSelect('q')
            ->orderBy('s.minScore', 'ASC');

        $quizId = $request->query->get('quiz_id');
        if ($quizId) {
            $queryBuilder->andWhere(':quizId MEMBER OF s.quizzes')
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

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $stressThreshold = new StressThreshold();

        $form = $this->createForm(StressThresholdType::class, $stressThreshold);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($stressThreshold);
            $this->entityManager->flush();

            $this->addSuccessFlash('Le seuil de stress a été créé avec succès.');

            return $this->redirectToRoute('app_admin_stress_threshold_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/stress_threshold/new.html.twig', [
            'stress_threshold' => $stressThreshold,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editer', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StressThreshold $stressThreshold): Response
    {
        $form = $this->createForm(StressThresholdType::class, $stressThreshold);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addSuccessFlash('Le seuil de stress a été mis à jour avec succès.');

            return $this->redirectToRoute('app_admin_stress_threshold_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/stress_threshold/edit.html.twig', [
            'stress_threshold' => $stressThreshold,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, StressThreshold $stressThreshold): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stressThreshold->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($stressThreshold);
            $this->entityManager->flush();
            $this->addSuccessFlash('Le seuil a été supprimé définitivement.');
        }

        return $this->redirectToRoute('app_admin_stress_threshold_index', [], Response::HTTP_SEE_OTHER);
    }
}
