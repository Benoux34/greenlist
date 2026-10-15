<?php

declare(strict_types=1);

namespace App\Observation\Service;

use App\Observation\Dto\ObservationDetail;
use App\Observation\Entity\Observation;
use App\Place\Geocoder\GeocoderInterface;
use App\Species\Wikipedia\WikipediaClientInterface;

final readonly class DescribeObservation
{
    public function __construct(
        private GeocoderInterface $geocoder,
        private WikipediaClientInterface $wikipedia,
    ) {
    }

    public function __invoke(Observation $observation): ObservationDetail
    {
        return ObservationDetail::fromEntity(
            $observation,
            $this->geocoder->placeName($observation->getLatitude(), $observation->getLongitude()),
            $this->wikipedia->describe($observation->getSpecies()->getScientificName()),
        );
    }
}
