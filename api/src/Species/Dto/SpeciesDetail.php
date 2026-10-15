<?php

declare(strict_types=1);

namespace App\Species\Dto;

use App\Species\Entity\Category;
use App\Species\Entity\Species;
use App\Species\Wikipedia\SpeciesDescription;

final readonly class SpeciesDetail
{
    public function __construct(
        public int $inatId,
        public string $scientificName,
        public ?string $vernacularName,
        public Category $category,
        public string $kingdom,
        public ?string $phylum,
        public ?string $taxonClass,
        public ?string $taxonOrder,
        public ?string $family,
        public ?string $description,
        public ?string $wikipediaUrl,
    ) {
    }

    public static function fromEntity(Species $species, ?SpeciesDescription $description = null): self
    {
        return new self(
            $species->getInatId(),
            $species->getScientificName(),
            $species->getVernacularName(),
            $species->getCategory(),
            $species->getKingdom(),
            $species->getPhylum(),
            $species->getTaxonClass(),
            $species->getTaxonOrder(),
            $species->getFamily(),
            $description?->summary,
            $description?->url,
        );
    }
}
