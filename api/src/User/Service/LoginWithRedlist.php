<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Exception\EmailAlreadyUsedException;
use App\User\Redlist\RedlistIdentity;
use App\User\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class LoginWithRedlist
{
    public function __construct(private UserRepository $users)
    {
    }

    public function __invoke(RedlistIdentity $identity): User
    {
        $user = $this->users->findOneBy(['redlistId' => $identity->id]);
        if (null !== $user) {
            return $user;
        }

        // Redlist does not verify email addresses, so an existing account with
        // the same email is never linked automatically: the unique index on
        // email turns that case into an error instead.
        $user = User::fromRedlist($identity->id, mb_strtolower($identity->email), $identity->pseudo);

        try {
            $this->users->save($user);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyUsedException('Email already used.');
        }

        return $user;
    }
}
