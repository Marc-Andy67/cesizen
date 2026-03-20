<?php

namespace App\Controller\Admin;

use App\Entity\Response as QuizResponse;
use App\Form\ResponseType;
use App\Repository\ResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reponse', name: 'app_admin_response_')]
#[IsGranted('ROLE_ADMIN')]
class ResponseController extends AbstractCrudController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ResponseRepository $responseRepository,
        PaginatorInterface $paginator,
    ): Response {
        $queryBuilder = $responseRepository->createQueryBuilder('r')
            ->innerJoin('r.question', 'q')
            ->orderBy('q.title', 'ASC')
            ->addOrderBy('r.position', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/response/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quizResponse = new QuizResponse();
        $quizResponse->setIsActive(true);
        $quizResponse->setPosition(1);
        $quizResponse->setPoints(0);

        $form = $this->createForm(ResponseType::class, $quizResponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $quizResponse->getQuestion();
            if ($question !== null) {
                $question->addResponse($quizResponse);
            } else {
                $this->addFlash('error', 'Veuillez sélectionner une question.');
                return $this->render('admin/response/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $em->persist($quizResponse);
            $em->flush();
            $this->addSuccessFlash('Réponse créée avec succès.');

            // On peut rediriger vers la même question ou vers la liste
            return $this->redirectToRoute('app_admin_response_index');
        }

        return $this->render('admin/response/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/editer', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(QuizResponse $quizResponse, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ResponseType::class, $quizResponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addSuccessFlash('Réponse modifiée avec succès.');

            return $this->redirectToRoute('app_admin_response_index');
        }

        return $this->render('admin/response/edit.html.twig', [
            'response' => $quizResponse,
            'form' => $form->createView(),
        ]);
    }
}
