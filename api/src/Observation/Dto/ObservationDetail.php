<?php

declare(strict_types=1);

namespace App\Observation\Dto;

use App\Observation\Entity\Observation;
use App\Species\Dto\SpeciesDetail;
use App\Species\Wikipedia\SpeciesDescription;

final readonly class ObservationDetail
{
    /**
     * @param list<string> $photos
     */
    public function __construct(
        public string $id,
        public SpeciesDetail $species,
        public \DateTimeImmutable $observedAt,
        public float $latitude,
        public float $longitude,
        public ?string $place,
        public ?int $count,
        public ?string $notes,
        public array $photos,
    ) {
    }

    public static function fromEntity(
        Observation $observation,
        ?string $place,
        ?SpeciesDescription $description,
    ): self
    {
        return new self(
            (string) $observation->getId(),
            SpeciesDetail::fromEntity($observation->getSpecies(), $description),
            $observation->getObservedAt(),
            $observation->getLatitude(),
            $observation->getLongitude(),
            $place,
            $observation->getCount(),
            $observation->getNotes(),
            $observation->getPhotos(),
        );
    }
}
