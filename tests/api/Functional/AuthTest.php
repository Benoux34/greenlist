<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Cookie;

final class AuthTest extends ApiTestCase
{
    public function testRegisteringCreatesTheAccount(): void
    {
        $user = $this->json('POST', '/api/auth/register', ['firstName' => ' Ana ', 'email' => 'Ana@Example.fr', 'password' => 'motdepasse123']);

        $this->assertStatus(201);
        self::assertSame('ana@example.fr', $user['email']);
        self::assertSame('Ana', $user['firstName']);
        self::assertArrayNotHasKey('password', $user);
    }

    public function testAnEmailCanOnlyBeUsedOnce(): void
    {
        UserFactory::createOne(['email' => 'ana@example.fr']);

        $this->json('POST', '/api/auth/register', ['firstName' => 'Ana', 'email' => 'ana@example.fr', 'password' => 'motdepasse123']);

        $this->assertStatus(409);
    }

    public function testRegisteringValidatesTheFields(): void
    {
        $this->json('POST', '/api/auth/register', ['firstName' => '', 'email' => 'not-an-email', 'password' => 'short']);

        $this->assertStatus(422);
    }

    public function testLoggingInSetsHttpOnlyCookies(): void
    {
        UserFactory::createOne(['email' => 'ana@example.fr']);

        $this->json('POST', '/api/auth/login', ['email' => 'ana@example.fr', 'password' => UserFactory::PASSWORD]);

        self::assertResponseIsSuccessful();
        $cookies = [];
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie;
        }
        self::assertArrayHasKey('BEARER', $cookies);
        self::assertTrue($cookies['BEARER']->isHttpOnly());
        self::assertTrue($cookies['BEARER']->isSecure());
        self::assertSame(Cookie::SAMESITE_LAX, $cookies['BEARER']->getSameSite());
        self::assertArrayHasKey('refresh_token', $cookies);
        self::assertTrue($cookies['refresh_token']->isHttpOnly());
    }

    public function testAWrongPasswordIsRefused(): void
    {
        UserFactory::createOne(['email' => 'ana@example.fr']);

        $this->json('POST', '/api/auth/login', ['email' => 'ana@example.fr', 'password' => 'wrong-password']);

        $this->assertStatus(401);
    }

    public function testTheCurrentUserNeedsASession(): void
    {
        $this->json('GET', '/api/me');
        $this->assertStatus(401);

        $user = UserFactory::createOne(['firstName' => 'Ana']);
        $this->loginAs($user);
        $me = $this->json('GET', '/api/me');

        $this->assertStatus(200);
        self::assertSame('Ana', $me['firstName']);
    }

    public function testLoggingOutClearsTheCookie(): void
    {
        $this->loginAs(UserFactory::createOne());

        $this->client->request('POST', '/api/auth/logout');

        $cleared = array_filter(
            $this->client->getResponse()->headers->getCookies(),
            static fn (Cookie $cookie): bool => 'BEARER' === $cookie->getName() && $cookie->isCleared(),
        );
        self::assertCount(1, $cleared);
    }
}
