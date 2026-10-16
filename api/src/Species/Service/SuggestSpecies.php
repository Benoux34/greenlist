<?php

declare(strict_types=1);

namespace App\Species\Service;

use App\Species\Dto\SpeciesSearch;
use App\Species\Dto\SpeciesSuggestion;
use App\Species\Exception\SpeciesLookupFailedException;
use App\Species\Inaturalist\InaturalistClientInterface;
use App\Species\Inaturalist\Taxon;

final readonly class SuggestSpecies
{
    private const int LIMIT = 8;

    public function __construct(
        private InaturalistClientInterface $inaturalist,
    ) {
    }

    /**
     * Species matching the text; with a point, those observed around it come
     * first, the most observed first.
     *
     * @return list<SpeciesSuggestion>
     */
    public function __invoke(SpeciesSearch $search): array
    {
        $taxa = $this->inaturalist->autocomplete(trim($search->q), $search->category);
        $counts = $this->countsNear($taxa, $search);

        usort($taxa, static fn (Taxon $a, Taxon $b): int => ($counts[$b->inatId] ?? 0) <=> ($counts[$a->inatId] ?? 0));

        return array_map(
            static fn (Taxon $taxon): SpeciesSuggestion => SpeciesSuggestion::fromTaxon($taxon, isset($counts[$taxon->inatId])),
            \array_slice($taxa, 0, self::LIMIT),
        );
    }

    /**
     * @param list<Taxon> $taxa
     *
     * @return array<int, int>
     */
    private function countsNear(array $taxa, SpeciesSearch $search): array
    {
        if (null === $search->latitude || null === $search->longitude) {
            return [];
        }

        try {
            return $this->inaturalist->observedNear(
                array_map(static fn (Taxon $taxon): int => $taxon->inatId, $taxa),
                $search->latitude,
                $search->longitude,
            );
        } catch (SpeciesLookupFailedException) {
            return [];
        }
    }
}
