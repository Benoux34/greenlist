<?php

declare(strict_types=1);

namespace App\Place\Geocoder;

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

    public function placeName(float $latitude, float $longitude): ?string
    {
        $key = \sprintf('photon_reverse_%.4f_%.4f', $latitude, $longitude);

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($latitude, $longitude): ?string {
                $item->expiresAfter(2592000);

                $place = $this->http
                    ->request('GET', 'reverse', ['query' => ['lat' => $latitude, 'lon' => $longitude, 'lang' => 'fr', 'limit' => 1]])
                    ->toArray()['features'][0]['properties'] ?? null;

                return null === $place ? null : $this->placeLabel($place);
            });
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $place
     */
    private function placeLabel(array $place): ?string
    {
        $parts = [
            $place['city'] ?? $place['locality'] ?? $place['name'] ?? '',
            $place['county'] ?? $place['state'] ?? '',
            $place['country'] ?? '',
        ];
        $label = implode(', ', array_unique(array_filter($parts, static fn (string $part): bool => '' !== $part)));

        return '' === $label ? null : $label;
    }
}
