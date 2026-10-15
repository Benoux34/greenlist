<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Double\FakeGeocoder;
use App\Tests\Factory\UserFactory;

final class PlaceNameTest extends ApiTestCase
{
    public function testAPointGetsTheNameOfTheClosestPlace(): void
    {
        $this->loginAs(UserFactory::createOne());

        $place = $this->json('GET', '/api/places/name?latitude=45.76&longitude=4.83');

        $this->assertStatus(200);
        self::assertSame(['label' => FakeGeocoder::PLACE], $place);
    }

    public function testCoordinatesMustBeOnEarth(): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->json('GET', '/api/places/name?latitude=95&longitude=4.83');

        $this->assertStatus(422);
    }

    public function testPlaceNamesNeedASession(): void
    {
        $this->json('GET', '/api/places/name?latitude=45.76&longitude=4.83');

        $this->assertStatus(401);
    }
}
