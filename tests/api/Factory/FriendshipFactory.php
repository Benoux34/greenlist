<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Friend\Entity\Friendship;
use App\User\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Friendship>
 */
final class FriendshipFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Friendship::class;
    }

    public static function between(User $user, User $other): void
    {
        self::createOne(['user' => $user, 'friend' => $other]);
        self::createOne(['user' => $other, 'friend' => $user]);
    }

    protected function defaults(): array
    {
        return [
            'user' => UserFactory::new(),
            'friend' => UserFactory::new(),
        ];
    }
}
