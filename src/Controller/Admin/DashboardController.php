<?php

namespace App\Controller\Admin;

use App\Repository\DocumentationRepository;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin_')]
class DashboardController extends AbstractCrudController
{
    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        DocumentationRepository $docRepo,
        QuizRepository $quizRepo,
    ): Response {
        $this->denyAccessUnlessAdmin();

        $totalUsers = $userRepository->count([]);
        $documentationCount = $docRepo->count(['isActive' => true]);
        $quizCount = $quizRepo->count(['isActive' => true]);

        return $this->render('admin/dashboard/index.html.twig', [
            'total_users' => $totalUsers,
            'documentation_count' => $documentationCount,
            'quiz_count' => $quizCount,
        ]);
    }
}
