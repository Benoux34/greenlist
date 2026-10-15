<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\User\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public const string PASSWORD = 'motdepasse123';

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'firstName' => self::faker()->firstName(),
            'hashedPassword' => password_hash(self::PASSWORD, \PASSWORD_BCRYPT, ['cost' => 4]),
        ];
    }
}
