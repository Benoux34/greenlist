<?php

declare(strict_types=1);

namespace App\Species\Wikipedia;

final readonly class SpeciesDescription
{
    public function __construct(
        public string $summary,
        public ?string $url,
    ) {
    }
}
