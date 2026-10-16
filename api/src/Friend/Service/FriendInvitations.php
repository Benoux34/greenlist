<?php

declare(strict_types=1);

namespace App\Friend\Service;

use App\Friend\Dto\InvitationLink;
use App\Friend\Entity\FriendInvitation;
use App\Friend\Exception\InvitationNotFoundException;
use App\Friend\Exception\OwnInvitationException;
use App\Friend\Repository\FriendInvitationRepository;
use App\Friend\Repository\FriendshipRepository;
use App\User\Entity\User;

final readonly class FriendInvitations
{
    public function __construct(
        private FriendInvitationRepository $invitations,
        private FriendshipRepository $friendships,
    ) {
    }

    public function create(User $inviter): InvitationLink
    {
        $token = bin2hex(random_bytes(16));
        $invitation = new FriendInvitation($inviter, self::hash($token), new \DateTimeImmutable());
        $this->invitations->save($invitation);

        return new InvitationLink($token, $invitation->getExpiresAt());
    }

    public function find(string $token): FriendInvitation
    {
        $invitation = $this->invitations->findOneBy(['tokenHash' => self::hash($token)]);
        if (null === $invitation || $invitation->isExpired(new \DateTimeImmutable())) {
            throw new InvitationNotFoundException('This invitation does not exist or has expired.');
        }

        return $invitation;
    }

    public function accept(string $token, User $user): void
    {
        $invitation = $this->find($token);
        $inviter = $invitation->getInviter();
        if ($inviter->getId()->equals($user->getId())) {
            throw new OwnInvitationException('Nobody can accept their own invitation.');
        }

        $this->invitations->remove($invitation);
        $this->friendships->befriend($inviter, $user);
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
