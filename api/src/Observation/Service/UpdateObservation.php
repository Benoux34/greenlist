<?php

declare(strict_types=1);

namespace App\Observation\Service;

use App\Observation\Dto\ObservationInput;
use App\Observation\Entity\Observation;
use App\Observation\Repository\ObservationRepository;
use App\Species\Service\SpeciesResolver;

final readonly class UpdateObservation
{
    public function __construct(
        private SpeciesResolver $speciesResolver,
        private ObservationRepository $observations,
    ) {
    }

    public function __invoke(Observation $observation, ObservationInput $input): void
    {
        $observation->edit(
            $this->speciesResolver->resolve($input->inatId),
            $input->observedAt,
            $input->latitude,
            $input->longitude,
            $input->count,
            $input->notes,
            $input->visibility,
        );

        $this->observations->save($observation);
    }
}
