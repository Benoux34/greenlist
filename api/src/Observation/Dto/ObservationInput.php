<?php

declare(strict_types=1);

namespace App\Observation\Dto;

use App\Observation\Entity\Visibility;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ObservationInput
{
    public function __construct(
        #[Assert\Positive]
        public int $inatId,

        #[Assert\LessThanOrEqual('+5 minutes', message: 'An observation cannot be in the future.')]
        public \DateTimeImmutable $observedAt,

        #[Assert\Range(min: -90, max: 90)]
        public float $latitude,

        #[Assert\Range(min: -180, max: 180)]
        public float $longitude,

        #[Assert\Positive]
        public ?int $count = null,

        #[Assert\Length(max: 2000)]
        public ?string $notes = null,

        public Visibility $visibility = Visibility::Friends,
    ) {
    }
}
