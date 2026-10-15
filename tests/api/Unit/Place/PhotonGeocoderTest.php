<?php

declare(strict_types=1);

namespace App\Tests\Unit\Place;

use App\Place\Geocoder\PhotonGeocoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PhotonGeocoderTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function places(): iterable
    {
        yield 'town, county and country' => [['name' => 'Mairie', 'city' => 'Chamonix-Mont-Blanc', 'county' => 'Haute-Savoie', 'state' => 'Auvergne-Rhône-Alpes', 'country' => 'France'], 'Chamonix-Mont-Blanc, Haute-Savoie, France'];
        yield 'locality when there is no town' => [['locality' => 'Les Bossons', 'state' => 'Auvergne-Rhône-Alpes', 'country' => 'France'], 'Les Bossons, Auvergne-Rhône-Alpes, France'];
        yield 'repeated names once' => [['city' => 'Paris', 'state' => 'Paris', 'country' => 'France'], 'Paris, France'];
    }

    /**
     * @param array<string, string> $properties
     */
    #[DataProvider('places')]
    public function testThePlaceNameIsBuiltFromTheClosestFeature(array $properties, string $expected): void
    {
        $http = new MockHttpClient([$this->features([['properties' => $properties]])], 'https://photon.komoot.io/');

        self::assertSame($expected, new PhotonGeocoder($http, new ArrayAdapter())->placeName(45.92, 6.87));
    }

    public function testNoFeatureGivesNoName(): void
    {
        $http = new MockHttpClient([$this->features([])], 'https://photon.komoot.io/');

        self::assertNull(new PhotonGeocoder($http, new ArrayAdapter())->placeName(40.0, -40.0));
    }

    public function testAnUnreachableServiceGivesNoName(): void
    {
        $http = new MockHttpClient([new MockResponse('', ['error' => 'timeout'])], 'https://photon.komoot.io/');

        self::assertNull(new PhotonGeocoder($http, new ArrayAdapter())->placeName(45.92, 6.87));
    }

    public function testNearbyPointsShareTheCachedName(): void
    {
        $http = new MockHttpClient([$this->features([['properties' => ['city' => 'Lyon', 'country' => 'France']]])], 'https://photon.komoot.io/');
        $geocoder = new PhotonGeocoder($http, new ArrayAdapter());

        $geocoder->placeName(45.764111, 4.835711);
        $geocoder->placeName(45.764149, 4.835689);

        self::assertSame(1, $http->getRequestsCount());
    }

    /**
     * @param list<array<string, mixed>> $features
     */
    private function features(array $features): MockResponse
    {
        return new MockResponse(json_encode(['features' => $features], \JSON_THROW_ON_ERROR));
    }
}
