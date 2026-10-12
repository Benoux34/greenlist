<?php

declare(strict_types=1);

namespace App\Species\Inaturalist;

use App\Species\Entity\Category;
use App\Species\Exception\SpeciesLookupFailedException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class InaturalistClient implements InaturalistClientInterface
{
    private const array KINGDOMS = [1 => 'Animalia', 47126 => 'Plantae', 47170 => 'Fungi'];

    public function __construct(
        #[Target('inaturalist.client')]
        private HttpClientInterface $http,
        private CacheInterface $cache,
    ) {
    }

    public function autocomplete(string $query, ?Category $category = null): array
    {
        $key = 'inat_autocomplete_'.md5(($category?->value ?? '').'|'.mb_strtolower($query));

        return $this->cache->get($key, function (ItemInterface $item) use ($query, $category): array {
            $item->expiresAfter(3600);

            $parameters = ['q' => $query, 'locale' => 'fr', 'rank' => 'species', 'per_page' => 8];
            foreach (self::KINGDOMS as $id => $kingdom) {
                if (Category::fromKingdom($kingdom) === $category) {
                    $parameters['taxon_id'] = $id;
                }
            }

            return array_map($this->toTaxon(...), $this->results('taxa/autocomplete', $parameters));
        });
    }

    public function find(int $inatId): ?Taxon
    {
        $results = $this->results('taxa/'.$inatId, ['locale' => 'fr']);

        return [] === $results ? null : $this->toTaxon($results[0]);
    }

    /**
     * @param array<string, scalar> $query
     *
     * @return list<array<string, mixed>>
     */
    private function results(string $path, array $query): array
    {
        try {
            return $this->http->request('GET', $path, ['query' => $query])->toArray()['results'] ?? [];
        } catch (ExceptionInterface $exception) {
            throw new SpeciesLookupFailedException('iNaturalist could not be reached.', previous: $exception);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toTaxon(array $row): Taxon
    {
        $ranks = array_column($row['ancestors'] ?? [], 'name', 'rank');
        $kingdom = $ranks['kingdom'] ?? null;
        foreach ($row['ancestor_ids'] ?? [] as $id) {
            $kingdom ??= self::KINGDOMS[$id] ?? null;
        }

        $vernacularName = $row['preferred_common_name'] ?? null;

        return new Taxon(
            inatId: $row['id'],
            scientificName: $row['name'],
            vernacularName: null === $vernacularName ? null : mb_ucfirst($vernacularName),
            rank: $row['rank'],
            kingdom: $kingdom ?? '',
            phylum: $ranks['phylum'] ?? null,
            taxonClass: $ranks['class'] ?? null,
            taxonOrder: $ranks['order'] ?? null,
            family: $ranks['family'] ?? null,
            photoUrl: $row['default_photo']['square_url'] ?? null,
        );
    }
}
