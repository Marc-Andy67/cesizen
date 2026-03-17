<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user', name: 'app_admin_user_')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractCrudController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator,
    ): Response {
        // Query paramètre pour une éventuelle recherche
        $query = $request->query->get('q', '');

        $queryBuilder = $userRepository->createQueryBuilder('u')
            ->orderBy('u.creationDate', 'DESC');

        if ($query) {
            $queryBuilder->andWhere('u.email LIKE :q OR u.name LIKE :q')
                         ->setParameter('q', '%'.$query.'%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // 10 par page (pagination KnpPaginator)
        );

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination,
            'search_query' => $query,
        ]);
    }

    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, Request $request): Response
    {
        // Validation CSRF simple
        if ($this->isCsrfTokenValid('toggle_active'.$user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(!$user->isActive());

            // Si on désactive, on force peut-être le logout (pour une future itération).

            $this->entityManager->flush();
            $this->addSuccessFlash('Le statut de l\'utilisateur a été modifié avec succès.');
        } else {
            $this->addErrorFlash('Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/{id}/toggle-role', name: 'toggle_role', methods: ['POST'])]
    public function toggleAdminRole(User $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('toggle_role'.$user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();

            // On vérifie qu'on ne s'enlève pas les droits à soi-même
            /** @var User $currentUser */
            $currentUser = $this->getUser();
            if ($user === $currentUser && in_array('ROLE_ADMIN', $roles)) {
                $this->addErrorFlash('Vous ne pouvez pas retirer vos propres droits administrateur.');

                return $this->redirectToRoute('app_admin_user_index');
            }

            if (in_array('ROLE_ADMIN', $roles)) {
                // Retire le rôle
                $roles = array_diff($roles, ['ROLE_ADMIN']);
                $this->addSuccessFlash('Les droits administrateur ont été retirés à '.$user->getEmail());
            } else {
                // Ajoute le rôle
                $roles[] = 'ROLE_ADMIN';
                $this->addSuccessFlash('L\'utilisateur '.$user->getEmail().' a été promu administrateur.');
            }

            $user->setRoles(array_unique($roles));
            $this->entityManager->flush();
        } else {
            $this->addErrorFlash('Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
