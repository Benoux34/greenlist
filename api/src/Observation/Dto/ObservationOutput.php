<?php

declare(strict_types=1);

namespace App\Observation\Dto;

use App\Observation\Entity\Observation;
use App\Species\Dto\SpeciesSummary;

final readonly class ObservationOutput
{
    public function __construct(
        public string $id,
        public SpeciesSummary $species,
        public \DateTimeImmutable $observedAt,
        public float $latitude,
        public float $longitude,
        public ?int $count,
        public ?string $notes,
    ) {
    }

    public static function fromEntity(Observation $observation): self
    {
        return new self(
            (string) $observation->getId(),
            SpeciesSummary::fromEntity($observation->getSpecies()),
            $observation->getObservedAt(),
            $observation->getLatitude(),
            $observation->getLongitude(),
            $observation->getCount(),
            $observation->getNotes(),
        );
    }
}
