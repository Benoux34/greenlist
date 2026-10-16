<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Factory\UserFactory;

final class SpeciesAutocompleteTest extends ApiTestCase
{
    public function testSuggestionsComeFromINaturalist(): void
    {
        $this->loginAs(UserFactory::createOne());

        $suggestions = $this->json('GET', '/api/species/autocomplete?q=renard');

        $this->assertStatus(200);
        self::assertSame([
            'inatId' => 42069,
            'scientificName' => 'Vulpes vulpes',
            'vernacularName' => 'Renard roux',
            'category' => 'fauna',
            'photoUrl' => null,
            'nearby' => false,
        ], $suggestions[0]);
        self::assertSame(['Renard roux', 'Renard polaire'], array_column($suggestions, 'vernacularName'));
    }

    public function testSpeciesSeenAroundThePointComeFirst(): void
    {
        $this->loginAs(UserFactory::createOne());

        $svalbard = $this->json('GET', '/api/species/autocomplete?q=renard&latitude=78.22&longitude=15.65');
        $this->assertStatus(200);
        self::assertSame(['Renard polaire', 'Renard roux'], array_column($svalbard, 'vernacularName'));
        self::assertSame([true, false], array_column($svalbard, 'nearby'));

        $lyon = $this->json('GET', '/api/species/autocomplete?q=renard&latitude=45.76&longitude=4.83');
        self::assertSame(['Renard roux', 'Renard polaire'], array_column($lyon, 'vernacularName'));
        self::assertSame([true, false], array_column($lyon, 'nearby'));
    }

    public function testThePointMustBeOnEarth(): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->json('GET', '/api/species/autocomplete?q=renard&latitude=95&longitude=4.83');

        $this->assertStatus(422);
    }

    public function testTheCategoryNarrowsTheSuggestions(): void
    {
        $this->loginAs(UserFactory::createOne());

        self::assertSame([], $this->json('GET', '/api/species/autocomplete?q=renard&category=flora'));
        self::assertCount(1, $this->json('GET', '/api/species/autocomplete?q=chene&category=flora') ?? []);
    }

    public function testTheTextNeedsTwoLetters(): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->json('GET', '/api/species/autocomplete?q=r');

        $this->assertStatus(422);
    }

    public function testSuggestionsNeedASession(): void
    {
        $this->json('GET', '/api/species/autocomplete?q=renard');

        $this->assertStatus(401);
    }
}
