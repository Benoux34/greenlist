<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Double\FakeGeocoder;
use App\Tests\Factory\ObservationFactory;
use App\Tests\Factory\SpeciesFactory;
use App\Tests\Factory\UserFactory;

final class ObservationTest extends ApiTestCase
{
    private const array INPUT = [
        'inatId' => 42069,
        'observedAt' => '2026-06-01T10:30:00+02:00',
        'latitude' => 45.76,
        'longitude' => 4.83,
        'count' => 2,
        'notes' => '  Près du ruisseau ',
    ];

    public function testTheMapListsOnlyMyObservationsNewestFirst(): void
    {
        $me = UserFactory::createOne();
        $older = ObservationFactory::createOne(['author' => $me, 'observedAt' => new \DateTimeImmutable('2026-01-10')]);
        $newer = ObservationFactory::createOne(['author' => $me, 'observedAt' => new \DateTimeImmutable('2026-03-10')]);
        ObservationFactory::createOne();
        $this->loginAs($me);

        $list = $this->json('GET', '/api/observations/map');

        $this->assertStatus(200);
        self::assertSame([(string) $newer->getId(), (string) $older->getId()], array_column($list, 'id'));
        self::assertSame(['inatId', 'scientificName', 'vernacularName', 'category'], array_keys($list[0]['species']));
    }

    public function testObservationsNeedASession(): void
    {
        $this->json('GET', '/api/observations/map');

        $this->assertStatus(401);
    }

    public function testCreatingAnObservationResolvesTheSpecies(): void
    {
        $this->loginAs(UserFactory::createOne());

        $created = $this->json('POST', '/api/observations', self::INPUT);

        $this->assertStatus(201);
        self::assertSame(['inatId' => 42069, 'scientificName' => 'Vulpes vulpes', 'vernacularName' => 'Renard roux', 'category' => 'fauna'], $created['species']);
        self::assertSame('2026-06-01T08:30:00+00:00', $created['observedAt']);
        self::assertSame(2, $created['count']);
        self::assertSame('Près du ruisseau', $created['notes']);
        self::assertSame([], $created['photos']);
        self::assertCount(1, $this->json('GET', '/api/observations/map'));
    }

    public function testAKnownSpeciesIsReused(): void
    {
        $fox = SpeciesFactory::fox();
        $this->loginAs(UserFactory::createOne());

        $this->json('POST', '/api/observations', self::INPUT);
        $this->json('POST', '/api/observations', self::INPUT);

        $this->assertStatus(201);
        SpeciesFactory::assert()->count(1);
        self::assertSame(42069, $fox->getInatId());
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidInputs(): iterable
    {
        yield 'date in the future' => [['observedAt' => '2099-01-01T00:00:00+00:00']];
        yield 'latitude out of range' => [['latitude' => 120]];
        yield 'longitude out of range' => [['longitude' => -200]];
        yield 'zero individuals' => [['count' => 0]];
        yield 'notes too long' => [['notes' => str_repeat('a', 2001)]];
        yield 'unknown species' => [['inatId' => 999]];
        yield 'a kingdom is not a species' => [['inatId' => 47126]];
    }

    /**
     * @param array<string, mixed> $change
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidInputs')]
    public function testInvalidObservationsAreRefused(array $change): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->json('POST', '/api/observations', [...self::INPUT, ...$change]);

        $this->assertStatus(422);
    }

    public function testTheDetailAddsThePlaceAndTheDescription(): void
    {
        $me = UserFactory::createOne();
        $observation = ObservationFactory::createOne(['author' => $me, 'species' => SpeciesFactory::fox()]);
        $this->loginAs($me);

        $detail = $this->json('GET', '/api/observations/'.$observation->getId());

        $this->assertStatus(200);
        self::assertSame(FakeGeocoder::PLACE, $detail['place']);
        self::assertSame('Le Renard roux est un mammifère omnivore.', $detail['species']['description']);
        self::assertSame('https://fr.wikipedia.org/wiki/Renard_roux', $detail['species']['wikipediaUrl']);
        self::assertSame(['Animalia', 'Chordata', 'Mammalia', 'Carnivora', 'Canidae'], [
            $detail['species']['kingdom'],
            $detail['species']['phylum'],
            $detail['species']['taxonClass'],
            $detail['species']['taxonOrder'],
            $detail['species']['family'],
        ]);
    }

    public function testSomeoneElsesObservationIsOffLimits(): void
    {
        $observation = ObservationFactory::createOne();
        $this->loginAs(UserFactory::createOne());
        $url = '/api/observations/'.$observation->getId();

        $this->json('GET', $url);
        $this->assertStatus(403);

        $this->json('PATCH', $url, self::INPUT);
        $this->assertStatus(403);

        $this->json('DELETE', $url);
        $this->assertStatus(403);
    }

    public function testAnUnknownObservationIsNotFound(): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->json('GET', '/api/observations/01900000-0000-7000-8000-000000000000');

        $this->assertStatus(404);
    }

    public function testEditingReplacesTheFields(): void
    {
        $me = UserFactory::createOne();
        $observation = ObservationFactory::createOne(['author' => $me, 'species' => SpeciesFactory::fox(), 'count' => 1]);
        $this->loginAs($me);

        $edited = $this->json('PATCH', '/api/observations/'.$observation->getId(), [
            ...self::INPUT,
            'inatId' => 56133,
            'latitude' => 48.85,
            'count' => null,
            'notes' => '',
        ]);

        $this->assertStatus(200);
        self::assertSame('Chêne pédonculé', $edited['species']['vernacularName']);
        self::assertSame('flora', $edited['species']['category']);
        self::assertSame(48.85, $edited['latitude']);
        self::assertNull($edited['count']);
        self::assertNull($edited['notes']);
    }

    public function testEditingValidatesTheFields(): void
    {
        $me = UserFactory::createOne();
        $observation = ObservationFactory::createOne(['author' => $me]);
        $this->loginAs($me);

        $this->json('PATCH', '/api/observations/'.$observation->getId(), [...self::INPUT, 'latitude' => 91]);

        $this->assertStatus(422);
    }

    public function testDeletingRemovesTheObservation(): void
    {
        $me = UserFactory::createOne();
        $observation = ObservationFactory::createOne(['author' => $me]);
        $this->loginAs($me);
        $url = '/api/observations/'.$observation->getId();

        $this->json('DELETE', $url);
        $this->assertStatus(204);

        $this->json('GET', $url);
        $this->assertStatus(404);
    }
}
