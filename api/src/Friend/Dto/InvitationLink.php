<?php

declare(strict_types=1);

namespace App\Friend\Dto;

final readonly class InvitationLink
{
    public function __construct(
        public string $token,
        public \DateTimeImmutable $expiresAt,
    ) {
    }
}
