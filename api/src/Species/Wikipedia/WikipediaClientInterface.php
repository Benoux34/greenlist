<?php

declare(strict_types=1);

namespace App\Species\Wikipedia;

interface WikipediaClientInterface
{
    /**
     * Opening paragraph of the French Wikipedia article on a species, or null when there is none.
     */
    public function describe(string $scientificName): ?SpeciesDescription;
}
