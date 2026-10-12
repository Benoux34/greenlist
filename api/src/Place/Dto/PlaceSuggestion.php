<?php

declare(strict_types=1);

namespace App\Place\Dto;

final readonly class PlaceSuggestion
{
    public function __construct(
        public string $label,
        public float $latitude,
        public float $longitude,
    ) {
    }
}
