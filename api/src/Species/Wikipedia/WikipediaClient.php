<?php

declare(strict_types=1);

namespace App\Species\Wikipedia;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class WikipediaClient implements WikipediaClientInterface
{
    public function __construct(
        #[Target('wikipedia.client')]
        private HttpClientInterface $http,
        private CacheInterface $cache,
    ) {
    }

    public function describe(string $scientificName): ?SpeciesDescription
    {
        try {
            return $this->cache->get('wikipedia_'.md5($scientificName), function (ItemInterface $item) use ($scientificName): ?SpeciesDescription {
                $item->expiresAfter(2592000);

                $response = $this->http->request('GET', 'page/summary/'.rawurlencode(str_replace(' ', '_', $scientificName)));
                if (404 === $response->getStatusCode()) {
                    return null;
                }

                $page = $response->toArray();
                $summary = trim($page['extract'] ?? '');
                if ('standard' !== ($page['type'] ?? null) || '' === $summary) {
                    return null;
                }

                return new SpeciesDescription($summary, $page['content_urls']['desktop']['page'] ?? null);
            });
        } catch (ExceptionInterface) {
            return null;
        }
    }
}
