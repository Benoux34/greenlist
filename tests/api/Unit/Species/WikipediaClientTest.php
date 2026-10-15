<?php

declare(strict_types=1);

namespace App\Tests\Unit\Species;

use App\Species\Wikipedia\WikipediaClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WikipediaClientTest extends TestCase
{
    private const string BASE = 'https://fr.wikipedia.org/api/rest_v1/';

    public function testAStandardPageGivesTheSummaryAndItsLink(): void
    {
        $url = null;
        $http = new MockHttpClient(function (string $method, string $requested) use (&$url): MockResponse {
            $url = $requested;

            return $this->page(['type' => 'standard', 'extract' => ' Le Renard roux… ', 'content_urls' => ['desktop' => ['page' => 'https://fr.wikipedia.org/wiki/Renard_roux']]]);
        }, self::BASE);

        $description = new WikipediaClient($http, new ArrayAdapter())->describe('Vulpes vulpes');

        self::assertSame(self::BASE.'page/summary/Vulpes_vulpes', $url);
        self::assertNotNull($description);
        self::assertSame('Le Renard roux…', $description->summary);
        self::assertSame('https://fr.wikipedia.org/wiki/Renard_roux', $description->url);
    }

    public function testAMissingPageGivesNothing(): void
    {
        $http = new MockHttpClient([new MockResponse('{}', ['http_code' => 404])], self::BASE);

        self::assertNull(new WikipediaClient($http, new ArrayAdapter())->describe('Nobody knows'));
    }

    public function testADisambiguationPageGivesNothing(): void
    {
        $http = new MockHttpClient([$this->page(['type' => 'disambiguation', 'extract' => 'Plusieurs sens'])], self::BASE);

        self::assertNull(new WikipediaClient($http, new ArrayAdapter())->describe('Amanita'));
    }

    public function testAnUnreachableServiceGivesNothing(): void
    {
        $http = new MockHttpClient([new MockResponse('', ['error' => 'timeout'])], self::BASE);

        self::assertNull(new WikipediaClient($http, new ArrayAdapter())->describe('Vulpes vulpes'));
    }

    /**
     * @param array<string, mixed> $page
     */
    private function page(array $page): MockResponse
    {
        return new MockResponse(json_encode($page, \JSON_THROW_ON_ERROR));
    }
}
