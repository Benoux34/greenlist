<?php

declare(strict_types=1);

namespace App\Observation\Dto;

use App\Species\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ObservationFilter
{
    public function __construct(
        public ?Category $category = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Range(min: 1, max: 100)]
        public int $perPage = 20,
    ) {
    }
}
