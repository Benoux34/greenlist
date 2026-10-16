<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Factory\FriendInvitationFactory;
use App\Tests\Factory\FriendshipFactory;
use App\Tests\Factory\UserFactory;
use App\User\Entity\User;

final class FriendTest extends ApiTestCase
{
    public function testAnInvitationMakesTwoFriends(): void
    {
        $ana = UserFactory::createOne(['firstName' => 'Ana']);
        $leo = UserFactory::createOne(['firstName' => 'Léo']);

        $token = $this->invite($ana);
        $this->assertStatus(201);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $token);

        $this->loginAs($leo);
        $invitation = $this->json('GET', "/api/friends/invitations/$token");
        $this->assertStatus(200);
        self::assertSame(['id' => (string) $ana->getId(), 'firstName' => 'Ana'], $invitation['inviter']);

        $this->json('POST', "/api/friends/invitations/$token/accept");
        $this->assertStatus(204);

        self::assertSame(['Ana'], array_column($this->json('GET', '/api/friends'), 'firstName'));
        $this->loginAs($ana);
        $friends = $this->json('GET', '/api/friends');
        self::assertSame([(string) $leo->getId()], array_column($friends, 'id'));
        self::assertSame(['id', 'firstName', 'since'], array_keys($friends[0]));
    }

    public function testAnInvitationServesOnce(): void
    {
        $token = $this->invite(UserFactory::createOne());
        $this->loginAs(UserFactory::createOne());
        $this->json('POST', "/api/friends/invitations/$token/accept");

        $this->loginAs(UserFactory::createOne());
        $this->json('GET', "/api/friends/invitations/$token");
        $this->assertStatus(404);
        $this->json('POST', "/api/friends/invitations/$token/accept");
        $this->assertStatus(404);
    }

    public function testAnInvitationExpiresAfterAWeek(): void
    {
        $token = str_repeat('ab', 16);
        FriendInvitationFactory::withToken($token, new \DateTimeImmutable('-8 days'));
        $this->loginAs(UserFactory::createOne());

        $this->json('GET', "/api/friends/invitations/$token");
        $this->assertStatus(404);
        $this->json('POST', "/api/friends/invitations/$token/accept");
        $this->assertStatus(404);
    }

    public function testAnUnknownInvitationIsNotFound(): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->json('POST', '/api/friends/invitations/'.str_repeat('0', 32).'/accept');

        $this->assertStatus(404);
    }

    public function testNobodyAcceptsTheirOwnInvitation(): void
    {
        $ana = UserFactory::createOne();
        $token = $this->invite($ana);

        $this->json('POST', "/api/friends/invitations/$token/accept");

        $this->assertStatus(422);
        self::assertSame([], $this->json('GET', '/api/friends'));
    }

    public function testFriendsAcceptingAgainStayFriendsOnce(): void
    {
        $ana = UserFactory::createOne();
        $leo = UserFactory::createOne();
        FriendshipFactory::between($ana, $leo);
        $token = $this->invite($ana);

        $this->loginAs($leo);
        $this->json('POST', "/api/friends/invitations/$token/accept");

        $this->assertStatus(204);
        self::assertCount(1, $this->json('GET', '/api/friends'));
    }

    public function testRemovingAFriendEndsItForBoth(): void
    {
        $ana = UserFactory::createOne();
        $leo = UserFactory::createOne();
        $mia = UserFactory::createOne(['firstName' => 'Mia']);
        FriendshipFactory::between($ana, $leo);
        FriendshipFactory::between($ana, $mia);
        $this->loginAs($ana);

        $this->json('DELETE', '/api/friends/'.$leo->getId());

        $this->assertStatus(204);
        self::assertSame(['Mia'], array_column($this->json('GET', '/api/friends'), 'firstName'));
        $this->loginAs($leo);
        self::assertSame([], $this->json('GET', '/api/friends'));
    }

    public function testFriendsNeedASession(): void
    {
        $this->json('GET', '/api/friends');
        $this->assertStatus(401);

        $this->json('POST', '/api/friends/invitations');
        $this->assertStatus(401);
    }

    private function invite(User $inviter): string
    {
        $this->loginAs($inviter);

        return $this->json('POST', '/api/friends/invitations')['token'];
    }
}
