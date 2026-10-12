<?php

declare(strict_types=1);

namespace App\Species\Dto;

use App\Species\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SpeciesSearch
{
    public function __construct(
        #[Assert\Length(min: 2, max: 100)]
        public string $q,
        public ?Category $category = null,
    ) {
    }
}
