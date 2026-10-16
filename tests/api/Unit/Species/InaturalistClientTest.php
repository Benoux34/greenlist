<?php

declare(strict_types=1);

namespace App\Tests\Unit\Species;

use App\Species\Entity\Category;
use App\Species\Exception\SpeciesLookupFailedException;
use App\Species\Inaturalist\InaturalistClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class InaturalistClientTest extends TestCase
{
    private const array FOX = [
        'id' => 42069,
        'name' => 'Vulpes vulpes',
        'preferred_common_name' => 'renard roux',
        'rank' => 'species',
        'ancestor_ids' => [48460, 1, 2, 355675],
        'default_photo' => ['square_url' => 'https://example.org/fox.jpg'],
    ];

    public function testAutocompleteMapsTheTaxa(): void
    {
        $http = new MockHttpClient([$this->results([self::FOX])], 'https://api.inaturalist.org/v1/');

        $taxa = new InaturalistClient($http, new ArrayAdapter())->autocomplete('renard');

        self::assertCount(1, $taxa);
        self::assertSame(42069, $taxa[0]->inatId);
        self::assertSame('Vulpes vulpes', $taxa[0]->scientificName);
        self::assertSame('Renard roux', $taxa[0]->vernacularName);
        self::assertSame('Animalia', $taxa[0]->kingdom);
        self::assertSame('https://example.org/fox.jpg', $taxa[0]->photoUrl);
    }

    public function testAutocompleteAsksForFrenchSpeciesOfTheCategory(): void
    {
        $url = null;
        $http = new MockHttpClient(function (string $method, string $requested) use (&$url): MockResponse {
            $url = $requested;

            return $this->results([]);
        }, 'https://api.inaturalist.org/v1/');

        new InaturalistClient($http, new ArrayAdapter())->autocomplete('chene', Category::Flora);

        self::assertIsString($url);
        self::assertStringStartsWith('https://api.inaturalist.org/v1/taxa/autocomplete?', $url);
        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
        self::assertSame(['q' => 'chene', 'locale' => 'fr', 'rank' => 'species', 'per_page' => '30', 'taxon_id' => '47126'], $query);
    }

    public function testAutocompleteIsCached(): void
    {
        $http = new MockHttpClient([$this->results([self::FOX])], 'https://api.inaturalist.org/v1/');
        $client = new InaturalistClient($http, new ArrayAdapter());

        $client->autocomplete('Renard');
        $client->autocomplete('renard');

        self::assertSame(1, $http->getRequestsCount());
    }

    public function testFindReadsTheTaxonomyFromTheAncestors(): void
    {
        $oak = [
            'id' => 56133,
            'name' => 'Quercus robur',
            'preferred_common_name' => 'chêne pédonculé',
            'rank' => 'species',
            'ancestors' => [
                ['rank' => 'kingdom', 'name' => 'Plantae'],
                ['rank' => 'phylum', 'name' => 'Tracheophyta'],
                ['rank' => 'class', 'name' => 'Magnoliopsida'],
                ['rank' => 'order', 'name' => 'Fagales'],
                ['rank' => 'family', 'name' => 'Fagaceae'],
            ],
        ];
        $http = new MockHttpClient([$this->results([$oak])], 'https://api.inaturalist.org/v1/');

        $taxon = new InaturalistClient($http, new ArrayAdapter())->find(56133);

        self::assertNotNull($taxon);
        self::assertSame(['Plantae', 'Tracheophyta', 'Magnoliopsida', 'Fagales', 'Fagaceae'], [$taxon->kingdom, $taxon->phylum, $taxon->taxonClass, $taxon->taxonOrder, $taxon->family]);
        self::assertSame('Chêne pédonculé', $taxon->vernacularName);
        self::assertNull($taxon->photoUrl);
    }

    public function testFindReturnsNullForAnUnknownId(): void
    {
        $http = new MockHttpClient([$this->results([])], 'https://api.inaturalist.org/v1/');

        self::assertNull(new InaturalistClient($http, new ArrayAdapter())->find(1));
    }

    public function testObservedNearCountsTheSightingsAroundAPoint(): void
    {
        $url = null;
        $http = new MockHttpClient(function (string $method, string $requested) use (&$url): MockResponse {
            $url = $requested;

            return $this->results([['count' => 60, 'taxon' => ['id' => 41526]], ['count' => 3, 'taxon' => ['id' => 41494]]]);
        }, 'https://api.inaturalist.org/v1/');

        $counts = new InaturalistClient($http, new ArrayAdapter())->observedNear([41526, 41494, 41472], 39.6, -9.07);

        self::assertSame([41526 => 60, 41494 => 3], $counts);
        self::assertIsString($url);
        self::assertStringStartsWith('https://api.inaturalist.org/v1/observations/species_counts?', $url);
        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
        self::assertSame(['lat' => '39.6', 'lng' => '-9.07', 'radius' => '50', 'verifiable' => 'true', 'taxon_id' => '41526,41494,41472', 'per_page' => '3'], $query);
    }

    public function testObservedNearIsCachedAroundTheSamePlace(): void
    {
        $http = new MockHttpClient([$this->results([])], 'https://api.inaturalist.org/v1/');
        $client = new InaturalistClient($http, new ArrayAdapter());

        $client->observedNear([41526], 39.6011, -9.0701);
        $client->observedNear([41526], 39.6049, -9.0749);

        self::assertSame(1, $http->getRequestsCount());
    }

    public function testObservedNearWithoutTaxaAsksNothing(): void
    {
        $http = new MockHttpClient([], 'https://api.inaturalist.org/v1/');

        self::assertSame([], new InaturalistClient($http, new ArrayAdapter())->observedNear([], 39.6, -9.07));
        self::assertSame(0, $http->getRequestsCount());
    }

    public function testAnUnreachableServiceIsReported(): void
    {
        $http = new MockHttpClient([new MockResponse('', ['error' => 'Connection refused'])], 'https://api.inaturalist.org/v1/');

        $this->expectException(SpeciesLookupFailedException::class);
        new InaturalistClient($http, new ArrayAdapter())->find(42069);
    }

    /**
     * @param list<array<string, mixed>> $results
     */
    private function results(array $results): MockResponse
    {
        return new MockResponse(json_encode(['results' => $results], \JSON_THROW_ON_ERROR));
    }
}
