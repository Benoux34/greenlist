<?php

declare(strict_types=1);

namespace App\Friend\Entity;

use App\Friend\Repository\FriendInvitationRepository;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FriendInvitationRepository::class)]
final class FriendInvitation
{
    public const int LIFETIME_DAYS = 7;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $inviter;

    #[ORM\Column(length: 64, unique: true)]
    private string $tokenHash;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    public function __construct(User $inviter, string $tokenHash, \DateTimeImmutable $now)
    {
        $this->id = Uuid::v7();
        $this->inviter = $inviter;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $now->modify(\sprintf('+%d days', self::LIFETIME_DAYS));
    }

    public function getInviter(): User
    {
        return $this->inviter;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(\DateTimeImmutable $now): bool
    {
        return $now > $this->expiresAt;
    }
}
