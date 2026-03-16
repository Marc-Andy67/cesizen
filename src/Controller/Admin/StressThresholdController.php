<?php

namespace App\Controller\Admin;

use App\Entity\StressThreshold;
use App\Form\StressThresholdType;
use App\Repository\StressThresholdRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $repository->createQueryBuilder('s')
            ->orderBy('s.minScore', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/stress_threshold/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    // Note: Normalement ces seuils sont fixes en BDD. On permet juste l'édition ici.
    // La création est bloquée car ils sont liés au code (Stratégies).
    
    #[Route('/{id}/editer', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(StressThreshold $threshold, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(StressThresholdType::class, $threshold);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addSuccessFlash('Seuil de stress modifié avec succès.');
            return $this->redirectToRoute('app_admin_stress_threshold_index');
        }

        return $this->render('admin/stress_threshold/edit.html.twig', [
            'threshold' => $threshold,
            'form' => $form->createView(),
        ]);
    }
}
