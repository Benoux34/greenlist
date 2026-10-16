<?php

declare(strict_types=1);

namespace App\Friend\Repository;

use App\Friend\Entity\Friendship;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
final class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    /**
     * @return list<Friendship>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->addSelect('u')
            ->join('f.friend', 'u')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->orderBy('u.firstName')
            ->getQuery()
            ->getResult();
    }

    public function areFriends(User $user, User $other): bool
    {
        return null !== $this->findOneBy(['user' => $user, 'friend' => $other]);
    }

    public function befriend(User $user, User $other): void
    {
        if ($this->areFriends($user, $other)) {
            return;
        }

        $this->getEntityManager()->persist(new Friendship($user, $other));
        $this->getEntityManager()->persist(new Friendship($other, $user));
        $this->getEntityManager()->flush();
    }

    public function unfriend(User $user, User $other): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->delete(Friendship::class, 'f')
            ->where('(f.user = :user AND f.friend = :other) OR (f.user = :other AND f.friend = :user)')
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->setParameter('other', $other->getId(), UuidType::NAME)
            ->getQuery()
            ->execute();
    }
}
