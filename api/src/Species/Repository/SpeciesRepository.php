<?php

declare(strict_types=1);

namespace App\Species\Repository;

use App\Species\Entity\Species;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Species>
 */
final class SpeciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Species::class);
    }

    public function add(Species $species): void
    {
        $this->getEntityManager()->persist($species);
    }
}
