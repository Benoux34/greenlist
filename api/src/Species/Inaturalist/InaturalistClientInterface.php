<?php

declare(strict_types=1);

namespace App\Species\Inaturalist;

use App\Species\Entity\Category;

interface InaturalistClientInterface
{
    /**
     * Species whose French or scientific name matches the text.
     *
     * @return list<Taxon>
     */
    public function autocomplete(string $query, ?Category $category = null): array;

    /**
     * One taxon with its full taxonomy, or null when the id does not exist.
     */
    public function find(int $inatId): ?Taxon;

    /**
     * How many times each of these taxa was observed around a point, by id.
     * Taxa never seen there are absent.
     *
     * @param list<int> $taxonIds
     *
     * @return array<int, int>
     */
    public function observedNear(array $taxonIds, float $latitude, float $longitude): array;
}
