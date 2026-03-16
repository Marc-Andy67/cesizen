<?php

namespace App\Controller\Front;

use App\Form\ProfileFormType;
use App\Service\RgpdService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil', name: 'app_profile_')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $assessments = $entityManager->getRepository(\App\Entity\Assessment::class)->findBy(
            ['owner' => $user],
            ['date' => 'ASC']
        );
        
        $chartDates = [];
        $chartScores = [];
        foreach ($assessments as $assessment) {
            $chartDates[] = $assessment->getDate()->format('d/m/Y');
            $chartScores[] = $assessment->getTotalScore();
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render('front/profile/index.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user,
            'assessments' => $assessments,
            'chartDates' => json_encode($chartDates),
            'chartScores' => json_encode($chartScores),
        ]);
    }

    #[Route('/export', name: 'export_data', methods: ['GET'])]
    public function exportData(RgpdService $rgpdService): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        $data = $rgpdService->exportUserData($user);

        return $this->json($data, Response::HTTP_OK, [
            'Content-Disposition' => 'attachment; filename="mes-donnees-cesizen.json"'
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['POST'])]
    public function deleteAccount(
        Request $request, 
        RgpdService $rgpdService,
        \Symfony\Bundle\SecurityBundle\Security $security
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            // Déconnexion de l'utilisateur
            $security->logout(false);
            
            // Anonymisation des données selon le RGPD
            $rgpdService->anonymizeUser($user);

            $this->addFlash('success', 'Votre compte a été supprimé definitivement conformément au RGPD.');
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('error', 'Token de sécurité invalide.');
        return $this->redirectToRoute('app_profile_index');
    }
}
