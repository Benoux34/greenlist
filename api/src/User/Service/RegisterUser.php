<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Dto\RegisterUserInput;
use App\User\Entity\User;
use App\User\Exception\EmailAlreadyUsedException;
use App\User\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    public function __invoke(RegisterUserInput $input): User
    {
        $user = new User(
            mb_strtolower($input->email),
            trim($input->firstName),
            $this->hasherFactory->getPasswordHasher(User::class)->hash($input->password),
        );

        try {
            $this->users->save($user);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyUsedException('Email already used.');
        }

        return $user;
    }
}
