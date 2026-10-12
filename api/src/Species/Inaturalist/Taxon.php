<?php

declare(strict_types=1);

namespace App\Species\Inaturalist;

final readonly class Taxon
{
    public function __construct(
        public int $inatId,
        public string $scientificName,
        public ?string $vernacularName,
        public string $rank,
        public string $kingdom,
        public ?string $phylum = null,
        public ?string $taxonClass = null,
        public ?string $taxonOrder = null,
        public ?string $family = null,
        public ?string $photoUrl = null,
    ) {
    }
}
