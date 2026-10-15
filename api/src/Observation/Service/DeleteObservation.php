<?php

declare(strict_types=1);

namespace App\Observation\Service;

use App\Observation\Entity\Observation;
use App\Observation\Repository\ObservationRepository;

final readonly class DeleteObservation
{
    public function __construct(
        private ObservationRepository $observations,
        private ObservationPhotos $photos,
    ) {
    }

    public function __invoke(Observation $observation): void
    {
        $this->observations->remove($observation);
        $this->photos->removeAll($observation);
    }
}
