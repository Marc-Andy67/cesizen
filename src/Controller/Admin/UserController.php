<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\AdminCreateUserType;
use App\Form\Admin\UserEditType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $user->setCreationDate(new \DateTimeImmutable());
        $user->setIsActive(true);
        $user->setRoles(['ROLE_ADMIN']);

        $form = $this->createForm(AdminCreateUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addSuccessFlash('Administrateur créé avec succès.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('USER_EDIT', $user);

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addSuccessFlash('Utilisateur modifié avec succès.');

            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, Request $request): Response
    {
        if (!$this->isGranted('USER_TOGGLE_ACTIVE', $user)) {
            $this->addErrorFlash('Impossible de suspendre un compte administrateur.');

            return $this->redirectToRoute('app_admin_user_index');
        }

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
        if (!$this->isGranted('USER_TOGGLE_ROLE', $user)) {
            $this->addErrorFlash('Impossible de rétrograder un autre compte administrateur.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        if ($this->isCsrfTokenValid('toggle_role'.$user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                // Retire le rôle
                $roles = array_diff($roles, ['ROLE_ADMIN']);
                $this->addSuccessFlash('Les droits administrateur ont été retirés à '.$user->getEmail());
            } else {
                // Ajoute le rôle
                $roles[] = 'ROLE_ADMIN';
                $this->addSuccessFlash('L\'utilisateur '.$user->getEmail().' a été promu administrateur.');

                // Règle 2 : Promotion admin force isActive = true
                if (!$user->isActive()) {
                    $user->setIsActive(true);
                    $this->addSuccessFlash('Le compte a été automatiquement réactivé suite à la promotion.');
                }
            }

            $user->setRoles(array_unique($roles));
            $this->entityManager->flush();
        } else {
            $this->addErrorFlash('Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete_user'.$user->getId(), $request->request->get('_token'))) {
            /** @var User $currentUser */
            $currentUser = $this->getUser();

            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $this->addErrorFlash('Impossible de supprimer un compte administrateur.');
            } elseif ($user === $currentUser) {
                $this->addErrorFlash('Impossible de supprimer votre propre compte.');
            } else {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
                $this->addSuccessFlash('Le compte utilisateur a été supprimé avec succès.');
            }
        } else {
            $this->addErrorFlash('Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/{id}/debloquer', name: 'unlock', methods: ['POST'])]
    public function unlock(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('unlock_user'.$user->getId(), $request->request->get('_token'))) {
            $user->setLockedUntil(null);
            $user->setFailedAttempt(0);
            $this->entityManager->flush();
            $this->addSuccessFlash('Le compte a été débloqué avec succès.');
        } else {
            $this->addErrorFlash('Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
