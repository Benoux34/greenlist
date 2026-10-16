<?php

declare(strict_types=1);

namespace App\Tests\Unit\User;

use App\User\Entity\PasswordReset;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class PasswordResetTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-10-16 10:00:00');
    }

    public function testTheRightCodeWorksForFifteenMinutes(): void
    {
        $reset = $this->reset();

        self::assertTrue($reset->matches('right', $this->now->modify('+15 minutes')));
        self::assertFalse($reset->matches('right', $this->now->modify('+15 minutes +1 second')));
    }

    public function testFiveMistakesBurnTheCode(): void
    {
        $reset = $this->reset();

        for ($i = 0; $i < 4; ++$i) {
            self::assertFalse($reset->matches('wrong', $this->now));
        }
        self::assertTrue($reset->matches('right', $this->now));

        self::assertFalse($reset->matches('wrong', $this->now));
        self::assertFalse($reset->matches('right', $this->now));
    }

    public function testANewCodeStartsOver(): void
    {
        $reset = $this->reset();
        for ($i = 0; $i < 5; ++$i) {
            $reset->matches('wrong', $this->now);
        }

        $later = $this->now->modify('+20 minutes');
        $reset->renew('new', $later);

        self::assertFalse($reset->matches('right', $later));
        self::assertTrue($reset->matches('new', $later));
    }

    public function testOnlyOneCodePerMinute(): void
    {
        $reset = $this->reset();

        self::assertTrue($reset->wasIssuedWithinAMinute($this->now->modify('+59 seconds')));
        self::assertFalse($reset->wasIssuedWithinAMinute($this->now->modify('+61 seconds')));
    }

    private function reset(): PasswordReset
    {
        return new PasswordReset(new User('ana@example.fr', 'Ana', 'hash'), 'right', $this->now);
    }
}
