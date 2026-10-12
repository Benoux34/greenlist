<?php

declare(strict_types=1);

namespace App\Observation\Service;

use App\Observation\Dto\CreateObservationInput;
use App\Observation\Entity\Observation;
use App\Observation\Repository\ObservationRepository;
use App\Species\Service\SpeciesResolver;
use App\User\Entity\User;

final readonly class CreateObservation
{
    public function __construct(
        private SpeciesResolver $speciesResolver,
        private ObservationRepository $observations,
    ) {
    }

    public function __invoke(User $author, CreateObservationInput $input): Observation
    {
        $notes = trim($input->notes ?? '');

        $observation = new Observation(
            $author,
            $this->speciesResolver->resolve($input->inatId),
            $input->observedAt->setTimezone(new \DateTimeZone('UTC')),
            $input->latitude,
            $input->longitude,
            $input->count,
            '' === $notes ? null : $notes,
        );

        $this->observations->save($observation);

        return $observation;
    }
}
