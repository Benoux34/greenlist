<?php

declare(strict_types=1);

namespace App\Observation\Repository;

use App\Observation\Entity\Observation;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
     * @return list<Observation>
     */
    public function findAllByAuthor(User $author): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('s')
            ->join('o.species', 's')
            ->andWhere('o.author = :author')
            ->setParameter('author', $author->getId(), UuidType::NAME)
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
