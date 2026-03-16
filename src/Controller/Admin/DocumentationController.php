<?php

namespace App\Controller\Admin;

use App\Entity\Documentation;
use App\Form\DocumentationType;
use App\Repository\DocumentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/documentation', name: 'app_admin_documentation_')]
#[IsGranted('ROLE_ADMIN')]
class DocumentationController extends AbstractCrudController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request, 
        DocumentationRepository $documentationRepository,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $documentationRepository->createQueryBuilder('d')
            ->orderBy('d.title', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/documentation/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'])]
    public function toggleActive(Documentation $documentation, Request $request): Response
    {
        if ($this->isCsrfTokenValid('toggle_active_doc'.$documentation->getId(), $request->request->get('_token'))) {
            $documentation->setIsActive(!$documentation->isActive());
            $this->entityManager->flush();
            $this->addSuccessFlash('Le statut de la documentation a été modifié avec succès.');
        } else {
            $this->addErrorFlash('Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_documentation_index');
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $documentation = new Documentation();
        // Par défaut, la documentation n'est pas publiée à la création.
        $documentation->setIsActive(false);

        $form = $this->createForm(DocumentationType::class, $documentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($documentation);
            $em->flush();
            $this->addSuccessFlash('Documentation créée avec succès.');
            return $this->redirectToRoute('app_admin_documentation_index');
        }

        return $this->render('admin/documentation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/editer', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Documentation $documentation, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DocumentationType::class, $documentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addSuccessFlash('Documentation modifiée avec succès.');
            return $this->redirectToRoute('app_admin_documentation_index');
        }

        return $this->render('admin/documentation/edit.html.twig', [
            'documentation' => $documentation,
            'form' => $form->createView(),
        ]);
    }
}
