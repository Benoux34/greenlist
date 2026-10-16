<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\User;
use App\User\Entity\RefreshToken;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->findOneBy(['email' => mb_strtolower($identifier)]);
    }

    public function revokeSessions(User $user): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM '.RefreshToken::class.' t WHERE t.username = :email')
            ->setParameter('email', $user->getUserIdentifier())
            ->execute();
    }
}
