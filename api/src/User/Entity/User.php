<?php

declare(strict_types=1);

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use App\User\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(nullable: true)]
    private ?string $password;

    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: true)]
    private ?Uuid $redlistId = null;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(string $email, string $firstName, ?string $hashedPassword)
    {
        $this->id = Uuid::v7();
        $this->email = $email;
        $this->firstName = $firstName;
        $this->password = $hashedPassword;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function fromRedlist(Uuid $redlistId, string $email, string $firstName): self
    {
        $user = new self($email, $firstName, null);
        $user->redlistId = $redlistId;

        return $user;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }
}
