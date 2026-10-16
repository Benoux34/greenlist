<?php

declare(strict_types=1);

namespace App\Species\Dto;

use App\Species\Entity\Category;
use App\Species\Inaturalist\Taxon;

final readonly class SpeciesSuggestion
{
    public function __construct(
        public int $inatId,
        public string $scientificName,
        public ?string $vernacularName,
        public Category $category,
        public ?string $photoUrl,
        public bool $nearby,
    ) {
    }

    public static function fromTaxon(Taxon $taxon, bool $nearby = false): self
    {
        return new self(
            $taxon->inatId,
            $taxon->scientificName,
            $taxon->vernacularName,
            Category::fromKingdom($taxon->kingdom),
            $taxon->photoUrl,
            $nearby
        );
    }
}
