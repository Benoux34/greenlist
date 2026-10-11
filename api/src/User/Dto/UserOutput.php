<?php

declare(strict_types=1);

namespace App\User\Dto;

use App\User\Entity\User;

final readonly class UserOutput
{
    public function __construct(
        public string $id,
        public string $email,
        public string $firstName,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            (string) $user->getId(),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getCreatedAt(),
        );
    }
}
