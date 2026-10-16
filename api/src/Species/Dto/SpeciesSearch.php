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

        #[Assert\Range(min: -90, max: 90)]
        public ?float $latitude = null,

        #[Assert\Range(min: -180, max: 180)]
        public ?float $longitude = null,
    ) {
    }
}
