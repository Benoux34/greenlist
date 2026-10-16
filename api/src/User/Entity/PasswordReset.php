<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\User\Repository\PasswordResetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PasswordResetRepository::class)]
final class PasswordReset
{
    public const int LIFETIME_MINUTES = 15;
    private const int MAX_ATTEMPTS = 5;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 64)]
    private string $codeHash;

    #[ORM\Column]
    private int $attempts;

    #[ORM\Column]
    private \DateTimeImmutable $issuedAt;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    public function __construct(User $user, string $codeHash, \DateTimeImmutable $now)
    {
        $this->id = Uuid::v7();
        $this->user = $user;
        $this->renew($codeHash, $now);
    }

    public function renew(string $codeHash, \DateTimeImmutable $now): void
    {
        $this->codeHash = $codeHash;
        $this->attempts = 0;
        $this->issuedAt = $now;
        $this->expiresAt = $now->modify(\sprintf('+%d minutes', self::LIFETIME_MINUTES));
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function wasIssuedWithinAMinute(\DateTimeImmutable $now): bool
    {
        return $this->issuedAt > $now->modify('-1 minute');
    }

    public function matches(string $codeHash, \DateTimeImmutable $now): bool
    {
        if ($this->attempts >= self::MAX_ATTEMPTS || $now > $this->expiresAt) {
            return false;
        }

        if (hash_equals($this->codeHash, $codeHash)) {
            return true;
        }

        ++$this->attempts;

        return false;
    }
}
