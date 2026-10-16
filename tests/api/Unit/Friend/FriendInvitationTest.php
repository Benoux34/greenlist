<?php

declare(strict_types=1);

namespace App\Tests\Unit\Friend;

use App\Friend\Entity\FriendInvitation;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class FriendInvitationTest extends TestCase
{
    public function testAnInvitationLastsAWeek(): void
    {
        $issuedAt = new \DateTimeImmutable('2026-10-16 12:00:00');
        $invitation = new FriendInvitation(new User('ana@example.fr', 'Ana', 'hash'), 'hash', $issuedAt);

        self::assertSame('2026-10-23 12:00:00', $invitation->getExpiresAt()->format('Y-m-d H:i:s'));
        self::assertFalse($invitation->isExpired(new \DateTimeImmutable('2026-10-23 12:00:00')));
        self::assertTrue($invitation->isExpired(new \DateTimeImmutable('2026-10-23 12:00:01')));
    }
}
