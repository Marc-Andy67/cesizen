<?php

namespace App\Controller\Front;

use App\Entity\Documentation;
use App\Repository\CategoryRepository;
use App\Repository\DocumentationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/documentation', name: 'app_documentation_')]
class DocumentationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        DocumentationRepository $documentationRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
    ): Response {
        $categoryIds = $request->query->all('categories');

        // On ne récupère que les documentations actives
        $queryBuilder = $documentationRepository->createQueryBuilder('d')
            ->where('d.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('d.title', 'ASC');

        if (!empty($categoryIds)) {
            $queryBuilder->innerJoin('d.categories', 'c')
                         ->andWhere('c.id IN (:categoryIds)')
                         ->setParameter('categoryIds', $categoryIds);
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9 // 9 par page pour un affichage en grille 3x3
        );

        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);

        return $this->render('front/documentation/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
            'current_categories' => $categoryIds,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Documentation $documentation): Response
    {
        // On vérifie que la documentation est bien active
        if (!$documentation->isActive()) {
            throw $this->createNotFoundException('Cette documentation n\'est pas disponible.');
        }

        return $this->render('front/documentation/show.html.twig', [
            'documentation' => $documentation,
        ]);
    }
}
