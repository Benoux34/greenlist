<?php

declare(strict_types=1);

namespace App\Tests\Unit\Species;

use App\Species\Dto\SpeciesSearch;
use App\Species\Dto\SpeciesSuggestion;
use App\Species\Entity\Category;
use App\Species\Exception\SpeciesLookupFailedException;
use App\Species\Inaturalist\InaturalistClientInterface;
use App\Species\Inaturalist\Taxon;
use App\Species\Service\SuggestSpecies;
use PHPUnit\Framework\TestCase;

final class SuggestSpeciesTest extends TestCase
{
    public function testWithoutAPointTheOrderOfINaturalistIsKept(): void
    {
        $suggestions = $this->suggest(new SpeciesSearch('dauphin'), $this->dolphins(3), new \LogicException('No point, no question.'));

        self::assertSame([1, 2, 3], $this->ids($suggestions));
        self::assertSame([false, false, false], array_map(static fn (SpeciesSuggestion $suggestion): bool => $suggestion->nearby, $suggestions));
    }

    public function testSpeciesSeenAroundThePointComeFirstMostSeenFirst(): void
    {
        $suggestions = $this->suggest(new SpeciesSearch('dauphin', null, 39.6, -9.07), $this->dolphins(5), [4 => 3, 2 => 60]);

        self::assertSame([2, 4, 1, 3, 5], $this->ids($suggestions));
        self::assertSame([true, true, false, false, false], array_map(static fn (SpeciesSuggestion $suggestion): bool => $suggestion->nearby, $suggestions));
    }

    public function testALocalSpeciesFarDownTheListStillMakesTheEight(): void
    {
        $suggestions = $this->suggest(new SpeciesSearch('dauphin', null, 39.6, -9.07), $this->dolphins(20), [17 => 5]);

        self::assertCount(8, $suggestions);
        self::assertSame(17, $suggestions[0]->inatId);
    }

    public function testAnUnreachableServiceStillGivesSuggestions(): void
    {
        $suggestions = $this->suggest(new SpeciesSearch('dauphin', null, 39.6, -9.07), $this->dolphins(3), new SpeciesLookupFailedException('down'));

        self::assertSame([1, 2, 3], $this->ids($suggestions));
    }

    /**
     * @param list<Taxon>                $taxa
     * @param array<int, int>|\Throwable $counts
     *
     * @return list<SpeciesSuggestion>
     */
    private function suggest(SpeciesSearch $search, array $taxa, array|\Throwable $counts): array
    {
        $inaturalist = new class($taxa, $counts) implements InaturalistClientInterface {
            /**
             * @param list<Taxon>                $taxa
             * @param array<int, int>|\Throwable $counts
             */
            public function __construct(private array $taxa, private array|\Throwable $counts)
            {
            }

            public function autocomplete(string $query, ?Category $category = null): array
            {
                return $this->taxa;
            }

            public function find(int $inatId): ?Taxon
            {
                return null;
            }

            public function observedNear(array $taxonIds, float $latitude, float $longitude): array
            {
                if ($this->counts instanceof \Throwable) {
                    throw $this->counts;
                }

                return $this->counts;
            }
        };

        return new SuggestSpecies($inaturalist)($search);
    }

    /**
     * @return list<Taxon>
     */
    private function dolphins(int $count): array
    {
        return array_map(
            static fn (int $id): Taxon => new Taxon($id, 'Delphinus '.$id, 'Dauphin '.$id, 'species', 'Animalia'),
            range(1, $count),
        );
    }

    /**
     * @param list<SpeciesSuggestion> $suggestions
     *
     * @return list<int>
     */
    private function ids(array $suggestions): array
    {
        return array_map(static fn (SpeciesSuggestion $suggestion): int => $suggestion->inatId, $suggestions);
    }
}
