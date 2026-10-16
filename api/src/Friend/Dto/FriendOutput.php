<?php

declare(strict_types=1);

namespace App\Friend\Dto;

use App\Friend\Entity\Friendship;

final readonly class FriendOutput
{
    public function __construct(
        public string $id,
        public string $firstName,
        public \DateTimeImmutable $since,
    ) {
    }

    public static function fromEntity(Friendship $friendship): self
    {
        $friend = $friendship->getFriend();

        return new self((string) $friend->getId(), $friend->getFirstName(), $friendship->getCreatedAt());
    }
}
