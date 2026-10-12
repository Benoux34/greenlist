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
}
