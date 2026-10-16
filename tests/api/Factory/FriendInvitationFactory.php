<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Friend\Entity\FriendInvitation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<FriendInvitation>
 */
final class FriendInvitationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return FriendInvitation::class;
    }

    public static function withToken(string $token, \DateTimeImmutable $issuedAt): FriendInvitation
    {
        return self::createOne(['tokenHash' => hash('sha256', $token), 'now' => $issuedAt]);
    }

    protected function defaults(): array
    {
        return [
            'inviter' => UserFactory::new(),
            'tokenHash' => hash('sha256', bin2hex(random_bytes(16))),
            'now' => new \DateTimeImmutable(),
        ];
    }
}
