<?php

declare(strict_types=1);

namespace App\User\Dto;

use App\User\Entity\User;

final readonly class UserSummary
{
    public function __construct(
        public string $id,
        public string $firstName,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self((string) $user->getId(), $user->getFirstName());
    }
}
