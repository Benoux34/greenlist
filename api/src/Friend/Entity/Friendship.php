<?php

declare(strict_types=1);

namespace App\Friend\Entity;

use App\Friend\Repository\FriendshipRepository;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
#[ORM\UniqueConstraint(name: 'friendship_user_friend_unique', fields: ['user', 'friend'])]
final class Friendship
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $friend;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(User $user, User $friend)
    {
        $this->id = Uuid::v7();
        $this->user = $user;
        $this->friend = $friend;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getFriend(): User
    {
        return $this->friend;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
