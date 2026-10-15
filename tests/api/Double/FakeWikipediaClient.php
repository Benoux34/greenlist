<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Species\Wikipedia\SpeciesDescription;
use App\Species\Wikipedia\WikipediaClientInterface;

final class FakeWikipediaClient implements WikipediaClientInterface
{
    public function describe(string $scientificName): ?SpeciesDescription
    {
        return 'Vulpes vulpes' === $scientificName
            ? new SpeciesDescription('Le Renard roux est un mammifère omnivore.', 'https://fr.wikipedia.org/wiki/Renard_roux')
            : null;
    }
}
