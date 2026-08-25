<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Retourne les comptes actifs inactifs depuis plus de $threshold (RGPD - droit à l'oubli).
     *
     * Un compte est inactif si sa dernière connexion est antérieure au seuil, ou, à défaut de
     * connexion, si sa date de création est antérieure au seuil. Les comptes déjà anonymisés
     * (isActive = false) sont exclus naturellement par le filtre isActive = true.
     *
     * @return User[]
     */
    public function findInactiveUsersOlderThan(\DateTimeImmutable $threshold): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->andWhere('(u.lastConnection IS NOT NULL AND u.lastConnection < :threshold) OR (u.lastConnection IS NULL AND u.creationDate < :threshold)')
            ->setParameter('active', true)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult()
        ;
    }
}
