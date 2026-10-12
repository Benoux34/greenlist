<?php

declare(strict_types=1);

namespace App\Species\Dto;

use App\Species\Entity\Species;
use App\Species\Entity\Category;

final readonly class SpeciesSummary
{
    public function __construct(
        public string $id,
        public string $scientificName,
        public ?string $vernacularName,
        public Category $category,
    ) {
    }

    public static function fromEntity(Species $species): self
    {
        return new self(
            (string) $species->getId(),
            $species->getScientificName(),
            $species->getVernacularName(),
            $species->getCategory(),
        );
    }
}
