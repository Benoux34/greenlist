<?php

declare(strict_types=1);

namespace App\Observation\Repository;

use App\Observation\Dto\ObservationFilter;
use App\Observation\Entity\Observation;
use App\Shared\Http\Paginated;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\Tools\Pagination\Window;
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
     * @return Paginated<Observation>
     */
    public function findPage(User $author, ObservationFilter $filter): Paginated
    {
        $query = $this->createQueryBuilder('o')
            ->addSelect('s')
            ->join('o.species', 's')
            ->andWhere('o.author = :author')
            ->setParameter('author', $author->getId(), UuidType::NAME)
            ->orderBy('o.observedAt', 'DESC')
            ->addOrderBy('o.id', 'DESC');

        if (null !== $filter->category) {
            $query->andWhere('s.category = :category')->setParameter('category', $filter->category);
        }

        if (null !== $filter->from) {
            $query->andWhere('o.observedAt >= :from')->setParameter('from', $filter->from);
        }

        if (null !== $filter->to) {
            $query->andWhere('o.observedAt < :to')->setParameter('to', $filter->to->modify('+1 day'));
        }

        $page = new OffsetPaginator(fetchJoinCollection: false)->paginate(
            $query,
            Window::fromPageNumberAndSize($filter->page, $filter->perPage),
        );

        return new Paginated($page->getItems(), $page->getTotalCount(), $filter->page, $filter->perPage);
    }

    public function save(Observation $observation): void
    {
        $this->getEntityManager()->persist($observation);
        $this->getEntityManager()->flush();
    }
}
