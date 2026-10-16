<?php

declare(strict_types=1);

namespace App\Friend\Repository;

use App\Friend\Entity\FriendInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FriendInvitation>
 */
final class FriendInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendInvitation::class);
    }

    public function save(FriendInvitation $invitation): void
    {
        $this->getEntityManager()->persist($invitation);
        $this->getEntityManager()->flush();
    }

    public function remove(FriendInvitation $invitation): void
    {
        $this->getEntityManager()->remove($invitation);
        $this->getEntityManager()->flush();
    }
}
