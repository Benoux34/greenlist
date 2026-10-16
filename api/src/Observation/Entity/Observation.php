<?php

declare(strict_types=1);

namespace App\Observation\Entity;

use App\Observation\Exception\PhotoNotFoundException;
use App\Observation\Exception\TooManyPhotosException;
use App\Observation\Repository\ObservationRepository;
use App\Species\Entity\Species;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ObservationRepository::class)]
#[ORM\Index(name: 'observation_author_observed_at_idx', fields: ['author', 'observedAt'])]
final class Observation
{
    public const int MAX_PHOTOS = 3;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Species $species;

    #[ORM\Column]
    private \DateTimeImmutable $observedAt;

    #[ORM\Column]
    private float $latitude;

    #[ORM\Column]
    private float $longitude;

    #[ORM\Column(nullable: true)]
    private ?int $count;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes;

    #[ORM\Column(enumType: Visibility::class, options: ['default' => Visibility::Friends->value])]
    private Visibility $visibility;

    #[ORM\Column(type: Types::JSON, options: ['default' => '[]'])]
    private array $photos = [];

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(
        User $author,
        Species $species,
        \DateTimeImmutable $observedAt,
        float $latitude,
        float $longitude,
        ?int $count = null,
        ?string $notes = null,
        Visibility $visibility = Visibility::Friends,
    ) {
        $this->id = Uuid::v7();
        $this->author = $author;
        $this->createdAt = new \DateTimeImmutable();
        $this->edit($species, $observedAt, $latitude, $longitude, $count, $notes, $visibility);
    }

    public function edit(
        Species $species,
        \DateTimeImmutable $observedAt,
        float $latitude,
        float $longitude,
        ?int $count,
        ?string $notes,
        Visibility $visibility,
    ): void {
        $notes = trim($notes ?? '');

        $this->species = $species;
        $this->observedAt = $observedAt->setTimezone(new \DateTimeZone('UTC'));
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->count = $count;
        $this->notes = '' === $notes ? null : $notes;
        $this->visibility = $visibility;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSpecies(): Species
    {
        return $this->species;
    }

    public function getObservedAt(): \DateTimeImmutable
    {
        return $this->observedAt;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getVisibility(): Visibility
    {
        return $this->visibility;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function addPhoto(string $name): void
    {
        if (\count($this->photos) >= self::MAX_PHOTOS) {
            throw new TooManyPhotosException(\sprintf('An observation holds %d photos at most.', self::MAX_PHOTOS));
        }

        $this->photos[] = $name;
    }

    public function removePhoto(string $name): void
    {
        if (!\in_array($name, $this->photos, true)) {
            throw new PhotoNotFoundException('This observation has no such photo.');
        }

        $this->photos = array_values(array_diff($this->photos, [$name]));
    }
}
