<?php

declare(strict_types=1);

namespace App\Place\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlacePoint
{
    public function __construct(
        #[Assert\Range(min: -90, max: 90)]
        public float $latitude,

        #[Assert\Range(min: -180, max: 180)]
        public float $longitude,
    ) {
    }
}
