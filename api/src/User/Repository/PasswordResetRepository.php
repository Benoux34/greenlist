<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\PasswordReset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordReset>
 */
final class PasswordResetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordReset::class);
    }

    public function findOneByEmail(string $email): ?PasswordReset
    {
        return $this->createQueryBuilder('r')
            ->addSelect('u')
            ->join('r.user', 'u')
            ->andWhere('u.email = :email')
            ->setParameter('email', mb_strtolower($email))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PasswordReset $reset): void
    {
        $this->getEntityManager()->persist($reset);
        $this->getEntityManager()->flush();
    }

    public function remove(PasswordReset $reset): void
    {
        $this->getEntityManager()->remove($reset);
        $this->getEntityManager()->flush();
    }
}
