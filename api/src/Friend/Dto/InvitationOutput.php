<?php

declare(strict_types=1);

namespace App\Friend\Dto;

use App\Friend\Entity\FriendInvitation;
use App\User\Dto\UserSummary;

final readonly class InvitationOutput
{
    public function __construct(
        public UserSummary $inviter,
        public \DateTimeImmutable $expiresAt,
    ) {
    }

    public static function fromEntity(FriendInvitation $invitation): self
    {
        return new self(UserSummary::fromEntity($invitation->getInviter()), $invitation->getExpiresAt());
    }
}
