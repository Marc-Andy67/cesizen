<?php

namespace App\Controller\Front;

use App\Form\IssueReportType;
use App\Service\GitHubIssueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_mentions')]
    public function mentionsLegales(): Response
    {
        return $this->render('front/legal/mentions_legales.html.twig');
    }

    #[Route('/cgu', name: 'app_legal_cgu')]
    public function cgu(): Response
    {
        return $this->render('front/legal/cgu.html.twig');
    }

    #[Route('/confidentialite', name: 'app_legal_confidentialite')]
    public function confidentialite(): Response
    {
        return $this->render('front/legal/confidentialite.html.twig');
    }

    #[Route('/accessibilite', name: 'app_accessibilite')]
    public function accessibilite(): Response
    {
        return $this->render('front/legal/accessibilite.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, GitHubIssueService $gitHubIssueService): Response
    {
        $form = $this->createForm(IssueReportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $created = $gitHubIssueService->createIssueFromReport(
                environnement: $data['environnement'],
                sujet: $data['sujet'],
                description: $data['description'],
                email: $data['email'] ?? null,
            );

            if ($created) {
                $this->addFlash('success', 'Merci, votre signalement a bien été transmis à notre équipe.');
            } else {
                $this->addFlash('error', "Votre signalement n'a pas pu être transmis automatiquement. Merci de réessayer plus tard ou de nous écrire directement par email.");
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('front/legal/contact.html.twig', [
            'issueReportForm' => $form,
        ]);
    }
}
