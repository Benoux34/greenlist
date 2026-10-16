<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Observation\Entity\Visibility;
use App\Tests\Factory\FriendshipFactory;
use App\Tests\Factory\ObservationFactory;
use App\Tests\Factory\UserFactory;
use App\User\Entity\User;

final class FriendObservationTest extends ApiTestCase
{
    private User $me;
    private User $friend;

    protected function setUp(): void
    {
        parent::setUp();

        $this->me = UserFactory::createOne(['firstName' => 'Ana']);
        $this->friend = UserFactory::createOne(['firstName' => 'Léo']);
        FriendshipFactory::between($this->me, $this->friend);
        $this->loginAs($this->me);
    }

    public function testTheMapAddsWhatFriendsShare(): void
    {
        $mine = ObservationFactory::createOne(['author' => $this->me, 'visibility' => Visibility::Private, 'observedAt' => new \DateTimeImmutable('2026-03-01')]);
        $shared = ObservationFactory::createOne(['author' => $this->friend, 'observedAt' => new \DateTimeImmutable('2026-04-01')]);
        ObservationFactory::createOne(['author' => $this->friend, 'visibility' => Visibility::Private]);
        ObservationFactory::createOne();

        $list = $this->json('GET', '/api/observations/map');

        $this->assertStatus(200);
        self::assertSame([(string) $shared->getId(), (string) $mine->getId()], array_column($list, 'id'));
        self::assertSame(['Léo', 'Ana'], array_column(array_column($list, 'author'), 'firstName'));
    }

    public function testAFriendsSharedObservationCanBeReadButNotChanged(): void
    {
        $url = '/api/observations/'.ObservationFactory::createOne(['author' => $this->friend])->getId();

        $detail = $this->json('GET', $url);
        $this->assertStatus(200);
        self::assertSame('Léo', $detail['author']['firstName']);

        $this->json('PATCH', $url, [
            'inatId' => 42069,
            'observedAt' => '2026-06-01T10:30:00+02:00',
            'latitude' => 45.76,
            'longitude' => 4.83,
        ]);
        $this->assertStatus(403);

        $this->json('DELETE', $url);
        $this->assertStatus(403);
    }

    public function testAPrivateObservationStaysHiddenFromFriends(): void
    {
        $observation = ObservationFactory::createOne(['author' => $this->friend, 'visibility' => Visibility::Private]);

        $this->json('GET', '/api/observations/'.$observation->getId());

        $this->assertStatus(403);
    }

    public function testAnExFriendsObservationsDisappear(): void
    {
        $observation = ObservationFactory::createOne(['author' => $this->friend]);
        $this->json('DELETE', '/api/friends/'.$this->friend->getId());

        self::assertSame([], $this->json('GET', '/api/observations/map'));
        $this->json('GET', '/api/observations/'.$observation->getId());
        $this->assertStatus(403);
    }
}
