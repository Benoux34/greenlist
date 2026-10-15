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
        self::assertSame([[
            'inatId' => 42069,
            'scientificName' => 'Vulpes vulpes',
            'vernacularName' => 'Renard roux',
            'category' => 'fauna',
            'photoUrl' => null,
        ]], $suggestions);
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
