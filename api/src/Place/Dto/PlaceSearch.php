<?php

declare(strict_types=1);

namespace App\Place\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceSearch
{
    public function __construct(
        #[Assert\Length(min: 3, max: 200)]
        public string $q,
    ) {
    }
}
