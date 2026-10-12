<?php

declare(strict_types=1);

namespace App\Place\Geocoder;

use App\Place\Dto\PlaceSuggestion;
use App\Place\Exception\PlaceLookupFailedException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PhotonGeocoder implements GeocoderInterface
{
    public function __construct(
        #[Target('photon.client')]
        private HttpClientInterface $http,
        private CacheInterface $cache,
    ) {
    }

    public function search(string $text): array
    {
        return $this->cache->get('photon_'.md5(mb_strtolower($text)), function (ItemInterface $item) use ($text): array {
            $item->expiresAfter(3600);

            try {
                $features = $this->http
                    ->request('GET', 'api/', ['query' => ['q' => $text, 'lang' => 'fr', 'limit' => 5]])
                    ->toArray()['features'] ?? [];
            } catch (ExceptionInterface $exception) {
                throw new PlaceLookupFailedException('Photon could not be reached.', previous: $exception);
            }

            return array_map($this->toSuggestion(...), $features);
        });
    }

    /**
     * @param array<string, mixed> $feature
     */
    private function toSuggestion(array $feature): PlaceSuggestion
    {
        $place = $feature['properties'] ?? [];
        [$longitude, $latitude] = $feature['geometry']['coordinates'];

        $street = trim(($place['housenumber'] ?? '').' '.($place['street'] ?? ''));
        $city = trim(($place['postcode'] ?? '').' '.($place['city'] ?? ''));

        $parts = [
            $place['name'] ?? '',
            $street,
            '' === $city ? ($place['state'] ?? '') : $city,
            $place['country'] ?? '',
        ];

        return new PlaceSuggestion(
            implode(', ', array_unique(array_filter($parts, static fn (string $part): bool => '' !== $part))),
            (float) $latitude,
            (float) $longitude,
        );
    }
}
