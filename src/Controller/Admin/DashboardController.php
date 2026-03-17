<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin_')]
class DashboardController extends AbstractCrudController
{
    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        // Récupérer quelques statistiques basiques (ex: nb total d'utilisateurs)
        $totalUsers = $userRepository->count([]);

        return $this->render('admin/dashboard/index.html.twig', [
            'total_users' => $totalUsers,
        ]);
    }
}
