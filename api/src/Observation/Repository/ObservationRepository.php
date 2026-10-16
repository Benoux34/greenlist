<?php

declare(strict_types=1);

namespace App\Observation\Repository;

use App\Observation\Entity\Observation;
use App\Observation\Entity\Visibility;
use App\User\Entity\User;
use App\Friend\Entity\Friendship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<Observation>
 */
final class ObservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Observation::class);
    }

    /**
     * The viewer's observations and those their friends share with them.
     *
     * @return list<Observation>
     */
    public function findVisibleTo(User $viewer): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('s', 'a')
            ->join('o.species', 's')
            ->join('o.author', 'a')
            ->leftJoin(Friendship::class, 'f', Join::WITH, 'f.user = :viewer AND f.friend = o.author')
            ->andWhere('o.author = :viewer OR (f.id IS NOT NULL AND o.visibility = :shared)')
            ->setParameter('viewer', $viewer->getId(), UuidType::NAME)
            ->setParameter('shared', Visibility::Friends)
            ->orderBy('o.observedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function save(Observation $observation): void
    {
        $this->getEntityManager()->persist($observation);
        $this->getEntityManager()->flush();
    }

    public function remove(Observation $observation): void
    {
        $this->getEntityManager()->remove($observation);
        $this->getEntityManager()->flush();
    }
}
