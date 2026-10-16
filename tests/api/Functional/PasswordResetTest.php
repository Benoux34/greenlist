<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Factory\UserFactory;
use Symfony\Component\Mime\Email;

final class PasswordResetTest extends ApiTestCase
{
    private const string EMAIL = 'ana@example.fr';

    public function testAKnownAddressReceivesACode(): void
    {
        UserFactory::createOne(['email' => self::EMAIL, 'firstName' => 'Ana']);

        $this->json('POST', '/api/auth/password/forgot', ['email' => 'Ana@Example.fr']);

        $this->assertStatus(204);
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', self::EMAIL);
        self::assertEmailAddressContains($email, 'From', 'no-reply@fauneflore.fr');
        self::assertEmailTextBodyContains($email, 'Bonjour Ana');
        self::assertStringContainsString($this->sentCode(), (string) $email->getHtmlBody());
    }

    public function testAnUnknownAddressGetsTheSameAnswer(): void
    {
        $this->json('POST', '/api/auth/password/forgot', ['email' => 'personne@example.fr']);

        $this->assertStatus(204);
        self::assertEmailCount(0);
    }

    public function testOnlyOneCodePerMinute(): void
    {
        UserFactory::createOne(['email' => self::EMAIL]);
        $this->json('POST', '/api/auth/password/forgot', ['email' => self::EMAIL]);

        $this->json('POST', '/api/auth/password/forgot', ['email' => self::EMAIL]);

        $this->assertStatus(204);
        self::assertEmailCount(0);
    }

    public function testTheCodeLetsMeChooseANewPassword(): void
    {
        UserFactory::createOne(['email' => self::EMAIL]);
        $this->json('POST', '/api/auth/password/forgot', ['email' => self::EMAIL]);
        $code = $this->sentCode();

        $this->json('POST', '/api/auth/password/check', ['email' => self::EMAIL, 'code' => strtolower($code)]);
        $this->assertStatus(204);

        $this->json('POST', '/api/auth/password/reset', ['email' => self::EMAIL, 'code' => $code, 'password' => 'nouveaumotdepasse']);
        $this->assertStatus(204);

        $this->json('POST', '/api/auth/login', ['email' => self::EMAIL, 'password' => UserFactory::PASSWORD]);
        $this->assertStatus(401);
        $this->json('POST', '/api/auth/login', ['email' => self::EMAIL, 'password' => 'nouveaumotdepasse']);
        self::assertResponseIsSuccessful();

        $this->json('POST', '/api/auth/password/check', ['email' => self::EMAIL, 'code' => $code]);
        $this->assertStatus(422);
    }

    public function testChangingThePasswordSignsOutEverywhere(): void
    {
        UserFactory::createOne(['email' => self::EMAIL]);
        $this->json('POST', '/api/auth/login', ['email' => self::EMAIL, 'password' => UserFactory::PASSWORD]);
        self::assertResponseIsSuccessful();

        $this->json('POST', '/api/auth/password/forgot', ['email' => self::EMAIL]);
        $this->json('POST', '/api/auth/password/reset', ['email' => self::EMAIL, 'code' => $this->sentCode(), 'password' => 'nouveaumotdepasse']);
        $this->assertStatus(204);

        $this->client->request('POST', '/api/auth/refresh');
        $this->assertStatus(401);
    }

    public function testFiveWrongCodesBurnTheCode(): void
    {
        UserFactory::createOne(['email' => self::EMAIL]);
        $this->json('POST', '/api/auth/password/forgot', ['email' => self::EMAIL]);
        $code = $this->sentCode();

        for ($i = 0; $i < 5; ++$i) {
            $this->json('POST', '/api/auth/password/check', ['email' => self::EMAIL, 'code' => '000000']);
            $this->assertStatus(422);
        }

        $this->json('POST', '/api/auth/password/check', ['email' => self::EMAIL, 'code' => $code]);
        $this->assertStatus(422);
    }

    public function testACodeWithoutARequestIsRefused(): void
    {
        UserFactory::createOne(['email' => self::EMAIL]);

        $this->json('POST', '/api/auth/password/reset', ['email' => self::EMAIL, 'code' => 'ABC234', 'password' => 'nouveaumotdepasse']);

        $this->assertStatus(422);
    }

    /**
     * @return iterable<string, array{string, array<string, string>}>
     */
    public static function invalidInputs(): iterable
    {
        yield 'not an email' => ['/api/auth/password/forgot', ['email' => 'not-an-email']];
        yield 'code too short' => ['/api/auth/password/check', ['email' => self::EMAIL, 'code' => 'ABC23']];
        yield 'code with a symbol' => ['/api/auth/password/check', ['email' => self::EMAIL, 'code' => 'ABC-23']];
        yield 'password too short' => ['/api/auth/password/reset', ['email' => self::EMAIL, 'code' => 'ABC234', 'password' => 'court']];
    }

    /**
     * @param array<string, string> $body
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidInputs')]
    public function testInvalidInputsAreRefused(string $url, array $body): void
    {
        $this->json('POST', $url, $body);

        $this->assertStatus(422);
    }

    private function sentCode(): string
    {
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertSame(1, preg_match('/\b([A-Z0-9]{6})\b/', (string) $email->getTextBody(), $match));

        return $match[1];
    }
}
