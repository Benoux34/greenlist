<?php

declare(strict_types=1);

namespace App\Observation\Dto;

use App\Observation\Entity\Visibility;
use App\Observation\Entity\Observation;
use App\Species\Dto\SpeciesSummary;
use App\User\Dto\UserSummary;

final readonly class ObservationOutput
{
    /**
     * @param list<string> $photos
     */
    public function __construct(
        public string $id,
        public UserSummary $author,
        public SpeciesSummary $species,
        public \DateTimeImmutable $observedAt,
        public float $latitude,
        public float $longitude,
        public ?int $count,
        public ?string $notes,
        public array $photos,
        public Visibility $visibility,
    ) {
    }

    public static function fromEntity(Observation $observation): self
    {
        return new self(
            (string) $observation->getId(),
            UserSummary::fromEntity($observation->getAuthor()),
            SpeciesSummary::fromEntity($observation->getSpecies()),
            $observation->getObservedAt(),
            $observation->getLatitude(),
            $observation->getLongitude(),
            $observation->getCount(),
            $observation->getNotes(),
            $observation->getPhotos(),
            $observation->getVisibility(),
        );
    }
}
